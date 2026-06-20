<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WeatherRiskService
{
    /**
     * Analyze normalized WeatherAI data and calculate an activity-specific risk score.
     */
    public function analyze(array $weatherData, string $activity): array
    {
        Log::info('Starting weather risk analysis', [
            'activity' => $activity,
        ]);

        $current = $weatherData['current'] ?? [];
        $alerts = $weatherData['alerts'] ?? [];
        $airQuality = $weatherData['airQuality'] ?? [];
        $hourly = $weatherData['hourly'] ?? [];
        $forecast = $weatherData['forecast'] ?? [];
        $bestHours = $weatherData['bestHours'] ?? [];

        $temperature = (float) $this->value($current, ['temperature'], 24);
        $feelsLike = (float) $this->value($current, ['feels_like'], $temperature);
        $windSpeed = (float) $this->value($current, ['wind_speed'], 0);
        $windGust = (float) $this->value($current, ['wind_gust'], 0);
        $rainChance = (float) $this->value($current, ['rain_probability'], 0);
        $uvIndex = (float) $this->value($current, ['uv_index'], 0);
        $aqi = (float) $this->value($airQuality, ['aqi', 'AQI'], 40);

        $nextSixHours = array_slice($hourly, 0, 6);
        $maxNextRain = $this->maxValue($nextSixHours, 'rain_probability');
        $maxNextWind = $this->maxValue($nextSixHours, 'wind_speed');
        $maxNextUv = $this->maxValue($nextSixHours, 'uv_index');

        $today = $forecast[0] ?? [];
        $dailyRain = (float) $this->value($today, ['precipitation_probability'], $rainChance);
        $dailyWindMax = (float) $this->value($today, ['wind_max'], $windSpeed);
        $tempMin = (float) $this->value($today, ['temp_min'], $temperature);
        $tempMax = (float) $this->value($today, ['temp_max'], $temperature);

        $score = 0;
        $reasons = [];

        if ($rainChance > 70 || $maxNextRain > 75 || $dailyRain > 80) {
            $score += 32;
            $reasons[] = 'High rain risk in the forecast window';
        } elseif ($rainChance > 40 || $maxNextRain > 45 || $dailyRain > 50) {
            $score += 18;
            $reasons[] = 'Possible rain expected';
        }

        if ($windGust > 35 || $dailyWindMax > 35) {
            $score += 24;
            $reasons[] = 'Strong wind gusts expected';
        } elseif ($windSpeed > 20 || $maxNextWind > 20 || $dailyWindMax > 20) {
            $score += 12;
            $reasons[] = 'Moderate wind conditions';
        }

        if ($feelsLike > 35 || $feelsLike < 5 || $tempMax > 35 || $tempMin < 5) {
            $score += 20;
            $reasons[] = 'Extreme temperature comfort risk';
        } elseif ($feelsLike > 30 || $feelsLike < 10 || $tempMax > 30 || $tempMin < 10) {
            $score += 10;
            $reasons[] = 'Uncomfortable temperature range';
        }

        if ($uvIndex >= 8 || $maxNextUv >= 8) {
            $score += 14;
            $reasons[] = 'Very high UV exposure risk';
        } elseif ($uvIndex >= 6 || $maxNextUv >= 6) {
            $score += 8;
            $reasons[] = 'Elevated UV exposure';
        }

        if ($aqi > 150) {
            $score += 25;
            $reasons[] = 'Unhealthy air quality';
        } elseif ($aqi > 100) {
            $score += 15;
            $reasons[] = 'Moderate air quality concern';
        }

        if (!empty($alerts)) {
            $score += 20;
            $reasons[] = 'Active weather alerts';
        }

        $score += $this->activityAdjustment(
            activity: $activity,
            rainChance: max($rainChance, $maxNextRain, $dailyRain),
            windSpeed: max($windSpeed, $windGust, $dailyWindMax),
            uvIndex: max($uvIndex, $maxNextUv),
            aqi: $aqi,
            tempMax: $tempMax
        );

        $score = min(100, max(0, $score));

        $result = [
            'score' => $score,
            'status' => $this->status($score),
            'level' => $this->level($score),
            'recommendation' => $this->recommendation($score, $activity, $bestHours),
            'best_time' => $this->bestTimeText($bestHours),
            'reasons' => $reasons ?: ['Weather conditions look generally favorable'],
            'metrics' => [
                'temperature' => $temperature,
                'feels_like' => $feelsLike,
                'rain_chance' => $rainChance,
                'max_next_rain' => $maxNextRain,
                'wind_speed' => $windSpeed,
                'wind_gust' => $windGust,
                'uv_index' => $uvIndex,
                'aqi' => $aqi,
            ],
        ];

        Log::info('Weather risk analysis completed', [
            'activity' => $activity,
            'score' => $score,
            'status' => $result['status'],
            'metrics' => $result['metrics'],
        ]);

        return $result;
    }

    private function activityAdjustment(
        string $activity,
        float $rainChance,
        float $windSpeed,
        float $uvIndex,
        float $aqi,
        float $tempMax
    ): int {
        $adjustment = match ($activity) {
            'running' => ($aqi > 100 ? 15 : 0)
                + ($rainChance > 40 ? 12 : 0)
                + ($uvIndex >= 7 ? 10 : 0)
                + ($tempMax > 30 ? 8 : 0),

            'laundry' => $rainChance > 30 ? 30 : 0,

            'outdoor_event' => ($rainChance > 30 ? 22 : 0)
                + ($windSpeed > 25 ? 12 : 0)
                + ($uvIndex >= 8 ? 8 : 0),

            'travel' => ($rainChance > 60 ? 15 : 0)
                + ($windSpeed > 35 ? 15 : 0),

            'farming' => ($rainChance < 20 ? 8 : 0)
                + ($tempMax > 32 ? 10 : 0),

            default => 0,
        };

        Log::debug('Activity risk adjustment calculated', [
            'activity' => $activity,
            'adjustment' => $adjustment,
        ]);

        return $adjustment;
    }

    private function status(int $score): string
    {
        return match (true) {
            $score <= 30 => 'Low Risk',
            $score <= 60 => 'Moderate Risk',
            default => 'High Risk',
        };
    }

    private function level(int $score): string
    {
        return match (true) {
            $score <= 30 => 'low',
            $score <= 60 => 'moderate',
            default => 'high',
        };
    }

    private function recommendation(int $score, string $activity, array $bestHours): string
    {
        $activityName = str_replace('_', ' ', $activity);
        $bestTime = $this->bestTimeText($bestHours);

        return match (true) {
            $score <= 30 => "Conditions look good for {$activityName}. {$bestTime}",
            $score <= 60 => "Conditions are usable for {$activityName}, but plan with caution. {$bestTime}",
            default => "Conditions may not be suitable for {$activityName}. Consider delaying or choosing a safer time. {$bestTime}",
        };
    }

    private function bestTimeText(array $bestHours): string
    {
        if (empty($bestHours)) {
            return 'No ideal time window was identified from the hourly forecast.';
        }

        $best = $bestHours[0];
        $time = $this->formatHour($best['time'] ?? null);

        return "Best suggested time: {$time}.";
    }

    private function formatHour(?string $time): string
    {
        if (!$time) {
            return 'Not available';
        }

        return date('D H:i', strtotime($time));
    }

    private function maxValue(array $items, string $key): float
    {
        if (empty($items)) {
            return 0;
        }

        return (float) collect($items)->max($key);
    }

    private function value(array $data, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] !== 'N/A') {
                return $data[$key];
            }
        }

        return $default;
    }
}
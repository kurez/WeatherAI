<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WeatherAiService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.weather_ai.base_url'), '/');
        $this->apiKey = config('services.weather_ai.key');

        Log::info('WeatherAiService initialized', [
            'base_url' => $this->baseUrl,
            'api_key_configured' => !empty($this->apiKey),
        ]);
    }

    public function getDashboardData(string $city): array
    {
        Log::info('Building weather dashboard data', [
            'city' => $city,
        ]);

        $geo = $this->getGeoLocation($city);

        $weather = $this->remember('dashboard_weather', $city, function () use ($geo) {
            return $this->get('/v1/weather', [
                'lat' => $geo['lat'],
                'lon' => $geo['lon'],
                'days' => 5,
                'ai' => 'true',
                'units' => 'metric',
                'lang' => 'en',
            ]);
        });
        
        Log::info('Weather API response', [
    'hourly_first' => $weather['hourly'][0] ?? null,
]);

        return [
            'geo' => $this->normalizeLocation($weather, $geo),
            'current' => $this->normalizeCurrent($weather),
            'forecast' => $this->normalizeForecast($weather),
            'hourly' => $this->normalizeHourly($weather),
            'bestHours' => $this->bestHours($weather),
            'alerts' => $this->getAlerts($city),
            'airQuality' => $this->getAirQuality($city),
            'ai_summary' => $this->extractAiSummary($weather),
            'raw' => $weather,
        ];
    }

    public function getCurrentWeather(string $city): array
    {
        return $this->getDashboardData($city)['current'];
    }

    public function getForecast(string $city): array
    {
        return $this->getDashboardData($city)['forecast'];
    }

    public function getAlerts(string $city): array
    {
        Log::info('Alerts requested, using fallback', [
            'city' => $city,
            'reason' => 'No public alerts endpoint available in current WeatherAI docs.',
        ]);

        return [];
    }

    public function getAirQuality(string $city): array
    {
        Log::info('Air quality requested, using fallback', [
            'city' => $city,
            'reason' => 'No public air-quality endpoint available in current WeatherAI docs.',
        ]);

        return [
            'aqi' => 42,
            'status' => 'Good',
        ];
    }

    public function getGeoLocation(string $city): array
    {
        Log::info('Resolving town coordinates', [
            'city' => $city,
        ]);

        $town = config("towns.$city");

        if (!$town) {
            Log::warning('Town not found in config, falling back to Nairobi', [
                'city' => $city,
            ]);

            $city = 'Nairobi';
            $town = config('towns.Nairobi');
        }

        return [
            'lat' => $town['lat'],
            'lon' => $town['lon'],
            'city' => $city,
            'region' => $town['region'],
            'country' => 'KE',
            'timezone' => 'Africa/Nairobi',
        ];
    }

    protected function get(string $endpoint, array $query = []): array
    {
        if (empty($this->apiKey)) {
            Log::error('WeatherAI API key missing');

            throw new Exception(
                'Weather-AI API key is missing. Please set WEATHER_AI_API_KEY in your .env file.'
            );
        }

        Log::info('Calling WeatherAI API', [
            'endpoint' => $endpoint,
            'query' => $query,
        ]);

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 300)
            ->get($this->baseUrl . $endpoint, $query);

        if ($response->failed()) {
            Log::error('WeatherAI API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => Str::limit($response->body(), 500),
            ]);

            throw new Exception(
                'Weather-AI API request failed: '
                . $response->status()
                . ' - '
                . Str::limit($response->body(), 300)
            );
        }

        Log::info('WeatherAI API request successful', [
            'endpoint' => $endpoint,
            'status' => $response->status(),
        ]);

        return $response->json() ?? [];
    }

    protected function remember(string $type, string $city, callable $callback): array
    {
        $key = 'weather_ai_' . $type . '_' . Str::slug($city);

        Log::debug('Checking WeatherAI cache', [
            'cache_key' => $key,
        ]);

        return Cache::remember($key, now()->addMinutes(15), $callback);
    }

    protected function normalizeCurrent(array $weather): array
    {
        $current = $weather['current'] ?? [];
        $hourly = $weather['hourly'][0] ?? [];

        $conditionCode = data_get($current, 'condition_code', data_get($hourly, 'condition_code'));

        return [
            'time' => data_get($current, 'time'),
            'temperature' => data_get($current, 'temperature', data_get($hourly, 'temperature', 'N/A')),
            'humidity' => data_get($hourly, 'humidity', 'N/A'),
            'feels_like' => data_get($hourly, 'feels_like', 'N/A'),
            'wind_speed' => data_get($current, 'wind_speed', data_get($hourly, 'wind_speed', 'N/A')),
            'wind_gust' => data_get($hourly, 'wind_gust', 'N/A'),
            'wind_direction' => data_get($current, 'wind_direction', 'N/A'),
            'rain_probability' => data_get($hourly, 'precipitation_probability', 0),
            'uv_index' => data_get($hourly, 'uv_index', 'N/A'),
            'condition_code' => $conditionCode,
            'condition' => $this->weatherCodeLabel($conditionCode),
            'icon' => data_get($current, 'icon', data_get($hourly, 'icon')),
        ];
    }

    protected function normalizeForecast(array $weather): array
    {
        return collect($weather['daily'] ?? [])
            ->map(function ($day) {
                $conditionCode = data_get($day, 'condition_code');

                return [
                    'date' => data_get($day, 'date'),
                    'temp_min' => data_get($day, 'temp_min'),
                    'temp_max' => data_get($day, 'temp_max'),
                    'temperature' => data_get($day, 'temp_max'),
                    'precipitation_sum' => data_get($day, 'precipitation_sum'),
                    'precipitation_probability' => data_get($day, 'precipitation_probability'),
                    'wind_max' => data_get($day, 'wind_max'),
                    'sunrise' => data_get($day, 'sunrise'),
                    'sunset' => data_get($day, 'sunset'),
                    'condition_code' => $conditionCode,
                    'condition' => $this->weatherCodeLabel($conditionCode),
                    'icon' => data_get($day, 'icon'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function normalizeHourly(array $weather): array
    {
        return collect($weather['hourly'] ?? [])
            ->take(12)
            ->map(function ($hour) {
                $conditionCode = data_get($hour, 'condition_code');

                return [
                    'time' => data_get($hour, 'time'),
                    'temperature' => data_get($hour, 'temperature'),
                    'feels_like' => data_get($hour, 'feels_like'),
                    'humidity' => data_get($hour, 'humidity'),
                    'rain_probability' => data_get($hour, 'precipitation_probability'),
                    'wind_speed' => data_get($hour, 'wind_speed'),
                    'wind_gust' => data_get($hour, 'wind_gust'),
                    'uv_index' => data_get($hour, 'uv_index'),
                    'condition_code' => $conditionCode,
                    'condition' => $this->weatherCodeLabel($conditionCode),
                    'icon' => data_get($hour, 'icon'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function normalizeLocation(array $weather, array $fallbackGeo): array
    {
        $location = $weather['location'] ?? [];

        return [
            'lat' => data_get($location, 'lat', $fallbackGeo['lat'] ?? null),
            'lon' => data_get($location, 'lon', $fallbackGeo['lon'] ?? null),
            'requested_lat' => data_get($location, 'requested_lat', $fallbackGeo['lat'] ?? null),
            'requested_lon' => data_get($location, 'requested_lon', $fallbackGeo['lon'] ?? null),
            'timezone' => data_get($location, 'timezone', $fallbackGeo['timezone'] ?? 'Africa/Nairobi'),
            'country' => data_get($location, 'country', $fallbackGeo['country'] ?? 'KE'),
            'city' => $fallbackGeo['city'] ?? 'Selected town',
            'region' => $fallbackGeo['region'] ?? null,
        ];
    }

    protected function bestHours(array $weather): array
    {
        return collect($weather['hourly'] ?? [])
            ->map(function ($hour) {
                $rain = (float) data_get($hour, 'precipitation_probability', 0);
                $wind = (float) data_get($hour, 'wind_speed', 0);
                $uv = (float) data_get($hour, 'uv_index', 0);

                $score = 100 - ($rain * 0.6) - ($wind * 1.2) - ($uv > 7 ? 15 : 0);

                return [
                    'time' => data_get($hour, 'time'),
                    'temperature' => data_get($hour, 'temperature'),
                    'rain_probability' => $rain,
                    'wind_speed' => $wind,
                    'uv_index' => $uv,
                    'score' => round(max(0, min(100, $score))),
                    'icon' => data_get($hour, 'icon'),
                ];
            })
            ->sortByDesc('score')
            ->take(3)
            ->values()
            ->toArray();
    }

    protected function weatherCodeLabel(?string $code): string
    {
        return match ((string) $code) {
            '0' => 'Clear sky',
            '1' => 'Mainly clear',
            '2' => 'Partly cloudy',
            '3' => 'Overcast',
            '45', '48' => 'Foggy',
            '51', '53', '55' => 'Drizzle',
            '61', '63', '65' => 'Rain',
            '71', '73', '75' => 'Snow',
            '80', '81', '82' => 'Rain showers',
            '95' => 'Thunderstorm',
            default => 'Weather conditions',
        };
    }

    protected function extractAiSummary(array $weather): ?string
    {
        return data_get($weather, 'summary')
            ?? data_get($weather, 'ai_summary')
            ?? data_get($weather, 'ai.summary')
            ?? data_get($weather, 'data.summary')
            ?? null;
    }
}
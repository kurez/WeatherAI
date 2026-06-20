<?php

namespace App\Http\Controllers;

use App\Services\WeatherAiService;
use App\Services\WeatherRiskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class WeatherController extends Controller
{
    /**
     * Show the weather dashboard form.
     */
    public function index()
    {
        Log::info('Weather dashboard page viewed');

        return view('weather.index');
    }

    /**
     * Handle dashboard search and build the weather impact report.
     */
    public function search(
        Request $request,
        WeatherAiService $weatherAiService,
        WeatherRiskService $weatherRiskService
    ) {
        Log::info('Weather dashboard search submitted', [
            'city' => $request->input('city'),
            'activity' => $request->input('activity'),
        ]);

        $validated = $request->validate([
            'city' => ['required', 'string', 'max:100'],
            'activity' => [
                'required',
                'string',
                Rule::in([
                    'commute',
                    'running',
                    'laundry',
                    'outdoor_event',
                    'travel',
                    'farming',
                ]),
            ],
        ]);

        try {
            $weatherData = $weatherAiService->getDashboardData($validated['city']);

            $risk = $weatherRiskService->analyze(
                $weatherData,
                $validated['activity']
            );

            Log::info('Weather dashboard generated successfully', [
                'city' => $validated['city'],
                'activity' => $validated['activity'],
                'risk_score' => $risk['score'] ?? null,
                'risk_status' => $risk['status'] ?? null,
            ]);

            return view('weather.index', [
                'city' => $validated['city'],
                'activity' => $validated['activity'],
                'weatherData' => $weatherData,
                'risk' => $risk,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to generate weather dashboard', [
                'city' => $validated['city'] ?? null,
                'activity' => $validated['activity'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'weather' => 'Unable to fetch weather data right now. Please check your API key or try again shortly.',
                ]);
        }
    }
}
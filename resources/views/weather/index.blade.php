<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WeatherAI Impact Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 10%, rgba(34,211,238,.28), transparent 28%),
                radial-gradient(circle at 90% 5%, rgba(52,211,153,.18), transparent 28%),
                linear-gradient(135deg, #020617 0%, #0f172a 55%, #020617 100%);
            color: #e5e7eb;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .top-nav {
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: rgba(2, 6, 23, .72);
            backdrop-filter: blur(16px);
        }

        .glass, .premium-card {
            background: linear-gradient(180deg, rgba(255,255,255,.09), rgba(255,255,255,.035));
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 28px;
            box-shadow: 0 24px 80px rgba(0,0,0,.34);
            backdrop-filter: blur(18px);
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 5.3rem);
            font-weight: 900;
            letter-spacing: -0.06em;
            line-height: .95;
        }

        .muted { color: #94a3b8; }

        .section-kicker {
            color: #67e8f9;
            font-size: .76rem;
            letter-spacing: .22em;
            font-weight: 800;
            text-transform: uppercase;
        }

        .text-gradient {
            background: linear-gradient(90deg, #67e8f9, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-premium {
            background: linear-gradient(135deg, #22d3ee, #34d399);
            border: none;
            color: #020617;
            font-weight: 900;
            box-shadow: 0 16px 35px rgba(34,211,238,.25);
        }

        .btn-premium:hover {
            color: #020617;
            transform: translateY(-1px);
        }

        .badge-soft {
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.07);
            color: #e5e7eb;
            padding: .65rem .9rem;
            border-radius: 999px;
        }

        .metric-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: rgba(34,211,238,.12);
            color: #67e8f9;
            font-size: 1.3rem;
        }

        .mini-card, .forecast-card, .hour-card {
            background: rgba(2,6,23,.48);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px;
            padding: 1rem;
            height: 100%;
        }

        .risk-circle {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            margin: 0 auto;
            box-shadow: inset 0 0 30px rgba(255,255,255,.14), 0 24px 60px rgba(0,0,0,.38);
        }

        .risk-low { background: linear-gradient(135deg, #34d399, #14b8a6); }
        .risk-moderate { background: linear-gradient(135deg, #fbbf24, #f97316); }
        .risk-high { background: linear-gradient(135deg, #fb7185, #ef4444); }

        .activity-option {
            min-height: 78px;
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .9rem 1rem;
            border-radius: 18px;
            cursor: pointer;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            color: #cbd5e1;
            transition: all .2s ease;
        }

        .activity-option i {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: rgba(34,211,238,.12);
            color: #67e8f9;
        }

        .btn-check:checked + .activity-option {
            background: linear-gradient(135deg, rgba(34,211,238,.24), rgba(52,211,153,.18));
            border-color: rgba(103,232,249,.75);
            color: #fff;
        }

        .ts-control {
            background: rgba(255,255,255,.96) !important;
            border-radius: 18px !important;
            min-height: 58px;
            border: none !important;
            padding: 12px !important;
        }

        .ts-dropdown {
            background: #0f172a !important;
            border: 1px solid rgba(255,255,255,.08) !important;
            border-radius: 18px !important;
        }

        .ts-dropdown .option {
            color: #e2e8f0;
            padding: 12px 16px;
        }

        .ts-dropdown .active {
            background: rgba(34,211,238,.15) !important;
        }

        .weather-icon {
            width: 72px;
            height: 72px;
        }

        .hour-scroll {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: .5rem;
        }

        .hour-card {
            min-width: 155px;
        }
    </style>
</head>

<body>
    @php
    $number = function ($value, $fallback = 'N/A') {
        if ($value === null || $value === '' || $value === 'N/A') {
            return $fallback;
        }

        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return is_numeric($clean) ? rtrim(rtrim(number_format((float) $clean, 1), '0'), '.') : $fallback;
    };
@endphp

@php
    $activities = [
        'commute' => 'Commute',
        'running' => 'Running',
        'laundry' => 'Laundry',
        'outdoor_event' => 'Outdoor Event',
        'travel' => 'Travel',
        'farming' => 'Farming',
    ];

    $activityIcons = [
        'commute' => 'bi-car-front',
        'running' => 'bi-person-walking',
        'laundry' => 'bi-basket',
        'outdoor_event' => 'bi-calendar-event',
        'travel' => 'bi-airplane',
        'farming' => 'bi-flower1',
    ];

    $towns = [
        'Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika',
        'Machakos', 'Meru', 'Nyeri', 'Naivasha', 'Nanyuki', 'Embu',
        'Kericho', 'Kitale', 'Malindi', 'Garissa', 'Kakamega', 'Bungoma',
        'Busia', 'Narok', 'Isiolo', 'Voi', 'Kilifi', 'Lamu', 'Moyale'
    ];
@endphp

<nav class="top-nav py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <div class="section-kicker">WeatherAI</div>
            <div class="fw-bold text-white fs-5">Impact Dashboard</div>
        </div>
        <span class="badge rounded-pill text-bg-success px-3 py-2">
            <i class="bi bi-cloud-check me-1"></i> Real-Time Intelligence
        </span>
    </div>
</nav>

<main class="container py-5">
    <section class="row align-items-center g-4 mb-5">
        <div class="col-lg-7">
            <div class="section-kicker mb-3">AI Weather Intelligence</div>
            <h1 class="hero-title text-white">
                Plan smarter with <span class="text-gradient">weather impact</span> insights.
            </h1>
            <p class="lead muted mt-4">
                Transform WeatherAI forecasts into risk scores, best activity windows,
                hourly trends, and practical recommendations.
            </p>
            <div class="d-flex flex-wrap gap-3 mt-4">
                <span class="badge-soft"><i class="bi bi-shield-check me-2"></i>Risk scoring</span>
                <span class="badge-soft"><i class="bi bi-clock-history me-2"></i>Best hours</span>
                <span class="badge-soft"><i class="bi bi-cloud-sun me-2"></i>Hourly forecast</span>
            </div>
        </div>

        <div class="col-lg-5">
            <form method="POST" action="{{ secure_url(route('weather.search', [], false)) }}" class="glass p-4">
                @csrf

                <h2 class="h4 fw-bold text-white mb-1">Weather Impact Analysis</h2>
                <p class="muted mb-4">Select a town and activity to generate a report.</p>

                <div class="mb-4">
                    <label class="form-label text-white-50 mb-3">
                        <i class="bi bi-geo-alt-fill me-2 text-info"></i>Select Town
                    </label>

                    <select name="city" id="city" required>
                        <option value="">Search town...</option>
                        @foreach($towns as $town)
                            <option value="{{ $town }}" @selected(old('city', $city ?? '') === $town)>
                                {{ $town }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label text-white-50 mb-3">Activity</label>

                    <div class="row g-2">
                        @foreach ($activities as $key => $label)
                            @php $selectedActivity = old('activity', $activity ?? 'commute'); @endphp

                            <div class="col-6">
                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="activity"
                                    id="activity_{{ $key }}"
                                    value="{{ $key }}"
                                    required
                                    @checked($selectedActivity === $key)
                                >

                                <label class="activity-option w-100" for="activity_{{ $key }}">
                                    <i class="bi {{ $activityIcons[$key] ?? 'bi-cloud-sun' }}"></i>
                                    <span class="fw-bold">{{ $label }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-premium btn-lg w-100 rounded-4 py-3">
                    <i class="bi bi-stars me-2"></i> Analyze Conditions
                </button>

                @if ($errors->any())
                    <div class="alert alert-danger mt-4 rounded-4 mb-0">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
            </form>
        </div>
    </section>
    
    

    @isset($weatherData)
        @php
            $current = $weatherData['current'] ?? [];
            $forecast = $weatherData['forecast'] ?? [];
            $hourly = $weatherData['hourly'] ?? [];
            $bestHours = $weatherData['bestHours'] ?? [];
            $geo = $weatherData['geo'] ?? [];
            $aiSummary = $weatherData['ai_summary'] ?? null;

            $temperature = $current['temperature'] ?? 'N/A';
            $humidity = $current['humidity'] ?? 'N/A';
            $feelsLike = $current['feels_like'] ?? 'N/A';
            $wind = $current['wind_speed'] ?? 'N/A';
            $windGust = $current['wind_gust'] ?? 'N/A';
            $uv = $current['uv_index'] ?? 'N/A';
            $condition = $current['condition'] ?? 'Current conditions';
            $rain = $current['rain_probability'] ?? 0;
            $icon = $current['icon'] ?? null;

            $riskClass = match($risk['level']) {
                'low' => 'risk-low',
                'moderate' => 'risk-moderate',
                default => 'risk-high',
            };
        @endphp

        <section class="mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-end">
                <div>
                    <div class="section-kicker">Generated Report</div>
                    <h2 class="display-6 fw-bold text-white mt-2 mb-1">
                        {{ $city }} -{{ $activities[$activity] ?? ucfirst($activity) }}
                    </h2>
                    <p class="muted mb-0">
                        {{ $geo['city'] ?? $city }},
                        {{ $geo['region'] ?? 'Region unavailable' }}
                        {{ isset($geo['country']) ? ' - ' . $geo['country'] : '' }}
                    </p>
                </div>

                <span class="badge rounded-pill px-4 py-3 fs-6 text-bg-dark border border-light-subtle">
                    {{ $risk['status'] }}
                </span>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="premium-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <p class="muted mb-2">Now</p>
                            <div class="display-1 fw-bold text-white">{{ $temperature }}&deg;C</div>
                            <p class="fs-5 text-white mb-1">{{ $condition }}</p>
                            <p class="muted mb-0">Feels like {{ $feelsLike }}&deg;C</p>
                        </div>

                        
                            <div class="metric-icon"><i class="bi bi-cloud-sun"></i></div>
                       
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="premium-card p-4 h-100">
                    <p class="muted mb-3">Live conditions</p>

                    <div class="row g-3">
                        <div class="col-6"><div class="mini-card"><small class="muted">Rain</small><div class="h3 text-white mb-0">{{ $rain }}%</div></div></div>
                        <div class="col-6"><div class="mini-card"><small class="muted">Humidity</small><div class="h3 text-white mb-0">{{ $humidity }}%</div></div></div>
                        <div class="col-6"><div class="mini-card"><small class="muted">Wind</small><div class="h3 text-white mb-0">{{ $wind }}</div></div></div>
                        <div class="col-6"><div class="mini-card"><small class="muted">UV</small><div class="h3 text-white mb-0">{{ $uv }}</div></div></div>
                    </div>

                    <p class="muted small mt-3 mb-0">Wind gust: {{ $windGust }}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="premium-card p-4 h-100 text-center">
                    <p class="muted mb-3">Risk score</p>
                    <div class="risk-circle {{ $riskClass }}">
                        <span class="display-4 fw-bold text-dark">{{ $risk['score'] }}</span>
                    </div>
                    <h3 class="text-white mt-4 mb-0">{{ $risk['status'] }}</h3>
                </div>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-lg-12">
                <div class="premium-card p-4 h-100">
                    <div class="section-kicker mb-2">Recommendation</div>
                    <h3 class="text-white fw-bold lh-base">{{ $risk['recommendation'] }}</h3>

                    @if(!empty($risk['best_time']))
                        <p class="text-info mt-3 mb-0">
                            <i class="bi bi-clock-history me-2"></i>{{ $risk['best_time'] }}
                        </p>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        @foreach ($risk['reasons'] as $reason)
                            <span class="badge-soft">{{ $reason }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

    

        @if ($aiSummary)
            <section class="premium-card p-4 mb-4">
                <div class="section-kicker mb-2">AI Weather Summary</div>
                <p class="lead text-white mb-0">
                    {{ is_array($aiSummary) ? json_encode($aiSummary) : $aiSummary }}
                </p>
            </section>
        @endif

        <section class="premium-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="section-kicker">Hourly Forecast</div>
                    <h3 class="text-white mb-0">Next 12 hours</h3>
                </div>
                <i class="bi bi-clock text-info fs-3"></i>
            </div>

            <div class="hour-scroll">
                @forelse($hourly as $hour)
                    <div class="hour-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <small class="muted">
                                {{ isset($hour['time']) ? date('H:i', strtotime($hour['time'])) : 'N/A' }}
                            </small>
                            <!--@if(!empty($hour['icon']))-->
                            <!--    <img src="{{ $hour['icon'] }}" width="34" height="34" alt="">-->
                            <!--@endif-->
                        </div>

                        <div class="h3 text-white mb-1">{{ $hour['temperature'] ?? 'N/A' }}&deg;C</div>
                        <small class="muted d-block">{{ $hour['condition'] ?? 'Forecast' }}</small>
                        <small class="muted d-block mt-2">Rain {{ $hour['rain_probability'] ?? 0 }}%</small>
                        <small class="muted d-block">UV {{ $hour['uv_index'] ?? 0 }}</small>
                    </div>
                @empty
                    <p class="muted mb-0">Hourly forecast unavailable.</p>
                @endforelse
            </div>
        </section>

        <section class="premium-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="section-kicker">Daily Forecast</div>
                    <h3 class="text-white mb-0">Upcoming days</h3>
                </div>
                <i class="bi bi-calendar-week text-info fs-3"></i>
            </div>

            <div class="row g-3">
                @forelse ($forecast as $day)
                    <div class="col-md-4">
                        <div class="forecast-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <small class="muted">{{ $day['date'] ?? 'Forecast day' }}</small>
                             
                            </div>

                            <div class="h2 fw-bold text-white">
                                {{ $day['temp_max'] ?? $day['temperature'] ?? 'N/A' }}&deg;C
                            </div>

                            <p class="muted mb-3">{{ $day['condition'] ?? 'Forecast available' }}</p>

                            <div class="d-flex justify-content-between small muted">
                                <span>Min {{ $day['temp_min'] ?? 'N/A' }}&deg;C</span>
                                <span>Rain {{ $day['precipitation_probability'] ?? 0 }}%</span>
                            </div>

                            <div class="d-flex justify-content-between small muted mt-2">
                                <span>Wind {{ $day['wind_max'] ?? 'N/A' }}</span>
                                <span>{{ isset($day['sunrise']) ? date('H:i', strtotime($day['sunrise'])) : 'N/A' }} sunrise</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="forecast-card text-center py-5">
                            <i class="bi bi-cloud-slash fs-1 text-white-50"></i>
                            <p class="muted mt-3 mb-0">Forecast data was not available.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    @else
        <section class="premium-card text-center p-5 mt-5">
            <div class="metric-icon mx-auto mb-3">
                <i class="bi bi-cloud-sun"></i>
            </div>
            <h2 class="text-white fw-bold">Ready to generate your first report?</h2>
            <p class="muted mb-0">
                Select a town and activity above to generate an impact report powered by WeatherAI.
            </p>
        </section>
    @endisset
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new TomSelect('#city', {
            create: false,
            maxOptions: 100,
            placeholder: 'Search town...',
            sortField: { field: 'text', direction: 'asc' },
            render: {
                option: function (data, escape) {
                    return '<div><i class="bi bi-geo-alt-fill text-info me-2"></i>' + escape(data.text) + '</div>';
                },
                item: function (data, escape) {
                    return '<div><i class="bi bi-geo-alt-fill text-info me-2"></i>' + escape(data.text) + '</div>';
                }
            }
        });
    });
</script>
</body>
</html>

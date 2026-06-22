# Impact Dashboard

AI-powered weather impact analysis platform built with Laravel and the WeatherAI API.

The dashboard transforms weather forecasts into actionable insights by combining real-time weather intelligence, activity-specific risk scoring, hourly forecasting, and AI-generated recommendations to help users make smarter decisions.

## Features

* Real-time weather intelligence
* AI-generated weather summaries
* Activity-specific risk scoring
* Best-time recommendations
* Hourly forecast visualization
* Multi-day forecast outlook
* Location-based weather insights
* Premium responsive UI/UX
* Laravel 12 architecture
* WeatherAI API integration
* Smart caching for API optimization
* Detailed logging and monitoring

## Supported Activities

* Commute
* Running
* Laundry
* Outdoor Events
* Travel
* Farming

## Technology Stack

* Laravel 12
* PHP 8.2+
* Bootstrap 5
* Tom Select
* WeatherAI API
* MySQL
* Vite

## Screenshots

### Dashboard

The landing page provides a premium weather intelligence experience with town selection, activity-based analysis, and AI-powered recommendations.

```md
![Dashboard](screenshots/dashboard.png)
```

### Weather Analysis

Risk scoring and weather impact insights generated from real-time WeatherAI data.

```md
<img width="1920" height="1029" alt="image" src="https://github.com/user-attachments/assets/6d742671-649e-4d05-92db-c04b2701ba69" />

```

### Hourly Forecast

Detailed hourly weather trends including temperature, precipitation probability, wind speed, and UV index.

```md
![Hourly Forecast](screenshots/hourly-forecast.png)
```

### Daily Forecast

Multi-day forecast view showing weather conditions, temperature ranges, and precipitation outlook.

```md
![Daily Forecast](screenshots/daily-forecast.png)
```


## Installation

Clone the repository:

```bash
git clone https://github.com/kurez/WeatherAI.git
cd WeatherAI
```

Install dependencies:

```bash
composer install
npm install
```

Create environment file:

```bash
cp .env.example .env
```

Configure your environment variables:

```env
WEATHER_AI_API_KEY=
WEATHER_AI_BASE_URL=https://api.weather-ai.co
```

Generate application key:

```bash
php artisan key:generate
```

Run database migrations:

```bash
php artisan migrate
```

Build frontend assets:

```bash
npm run build
```

Start the application:

```bash
php artisan serve
```

## Environment Variables

| Variable            | Description            |
| ------------------- | ---------------------- |
| WEATHER_AI_API_KEY  | WeatherAI API Key      |
| WEATHER_AI_BASE_URL | WeatherAI API Base URL |

## Architecture

The application consists of:

* WeatherAiService – WeatherAI API integration
* WeatherRiskService – Risk scoring engine
* WeatherController – Request handling
* Premium Bootstrap UI – Dashboard experience

## Risk Analysis Engine

Impact Dashboard AI evaluates:

* Rain probability
* Wind speed
* Wind gusts
* UV exposure
* Temperature extremes
* Air quality
* Weather alerts
* Activity-specific risk factors

The platform then generates:

* Risk score (0–100)
* Risk classification
* Personalized recommendation
* Best activity windows

## Deployment

Production deployments should configure environment variables securely and never commit `.env` files to source control.

```env
APP_ENV=production
APP_DEBUG=false

WEATHER_AI_API_KEY=your_api_key
WEATHER_AI_BASE_URL=https://api.weather-ai.co
```

## License

MIT License

## Author

William Masai Kipkures

Built using Laravel and WeatherAI.

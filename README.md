# Impact Dashboard

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3)
![WeatherAI](https://img.shields.io/badge/API-WeatherAI-00BFFF)
![Deployment](https://img.shields.io/badge/Deployment-Render-46E3B7)

[![Live Demo](https://img.shields.io/badge/Live-Demo-success?style=for-the-badge)](https://weatherai-ghro.onrender.com)
[![GitHub Repo](https://img.shields.io/badge/GitHub-Repository-black?style=for-the-badge&logo=github)](https://github.com/kurez/WeatherAI)

AI-powered weather impact analysis platform that transforms weather forecasts into actionable insights through activity-specific risk scoring, AI-powered recommendations, and intelligent forecasting.

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

# Weather AI Impact Dashboard

A Laravel dashboard that integrates with Weather-AI APIs and converts weather data into activity-specific recommendations, risk scores, forecasts, alerts, and air quality insights.

## Features

- City-based weather lookup
- Current weather dashboard
- 5-day forecast
- Weather alerts
- Air quality insights
- Activity-specific risk scoring
- Recommendation engine
- Secure API key handling
- Laravel service-based architecture
- API response caching

## Tech Stack

- Laravel
- PHP
- Blade
- Tailwind CSS
- Laravel HTTP Client
- Laravel Cache

## Installation

```bash
git clone https://github.com/YOUR_USERNAME/weather-ai-impact-dashboard.git
cd weather-ai-impact-dashboard

composer install
npm install

cp .env.example .env
php artisan key:generate
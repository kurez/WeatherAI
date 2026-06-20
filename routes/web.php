<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeatherController::class, 'index'])->name('weather.index');

Route::post('/weather', [WeatherController::class, 'search'])->name('weather.search');

Route::get('/weather', function () {
    return redirect()->route('weather.index');
});

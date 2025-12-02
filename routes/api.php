<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictionController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// Rate limiting: 30 predictions per minute per IP
RateLimiter::for('predictions', function ($request) {
    return Limit::perMinute(30)->by($request->ip());
});

Route::post('/predict', [PredictionController::class, 'predict'])
    ->middleware('throttle:predictions');

Route::get('/history', [PredictionController::class, 'history']);

Route::delete('/predictions/{id}', [PredictionController::class, 'destroy']);


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;

Route::get('/events', [EventController::class, 'index']);

// booking route (auth will be added later)
Route::post('/bookings/{event}', [BookingController::class, 'store']);

// temporarily keep admin creation public for testing
Route::post('/admin/events', [EventController::class, 'store']);

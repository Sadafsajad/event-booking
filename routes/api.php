<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
Route::get('/events', [EventController::class, 'index']);

// booking route (auth will be added later)
Route::post('/bookings/{event}', [BookingController::class, 'store']);

// temporarily keep admin creation public for testing
Route::post('/admin/events', [EventController::class, 'store']);
});
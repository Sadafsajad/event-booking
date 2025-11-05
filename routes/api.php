<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ReportController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', fn(Request $request) => $request->user());

Route::middleware('auth:sanctum')->group(function () {

    /* ✅ Events & Booking */
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/bookings/{event}', [BookingController::class, 'store']);
    
    /*  Admin event creation */
    Route::post('/admin/events', [EventController::class, 'store']);
    Route::delete('/admin/events/{event}', [EventController::class, 'destroy']);
    Route::get('/admin/events/{event}/edit', [EventController::class, 'edit']);
    Route::put('/admin/events/{event}', [EventController::class, 'update']);

    /*  Reports API */
    Route::get('/reports', [ReportController::class, 'index']);
});

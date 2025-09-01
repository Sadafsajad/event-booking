<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReportController;

Route::get('/auth/{provider}', [SocialAuthController::class, 'redirectToProvider'])
    ->where('provider', 'google|github')->name('social.redirect');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])
    ->where('provider', 'google|github')->name('social.callback');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth'])->group(function () {
    // Events
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

    // Bookings
    Route::post('/events/{event}/book', [BookingController::class, 'store'])->name('bookings.store');
});

Route::middleware(['auth'])->prefix('reports')->group(function () {
    Route::get('/top5-last30', [ReportController::class, 'top5EventsLast30']);
    Route::get('/power-users-last-month', [ReportController::class, 'usersBookedMoreThan3LastMonth']);
    Route::get('/occupancy', [ReportController::class, 'occupancyPerEvent']);
});

require __DIR__ . '/auth.php';
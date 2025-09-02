<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SocialAuthController;

/* ---------- Social login ---------- */
Route::get('/auth/google', [SocialAuthController::class, 'googleRedirect'])->name('google.redirect');
Route::get('/auth/google/callback', [SocialAuthController::class, 'googleCallback'])->name('google.callback');

Route::get('/auth/github', [SocialAuthController::class, 'redirect'])->name('github.redirect');
Route::get('/auth/github/callback', [SocialAuthController::class, 'callback'])->name('github.callback');

/* ---------- Basics ---------- */
Route::get('/', fn() => view('welcome'));

Route::get('/dashboard', function () {
    return auth()->user()->is_admin
        ? redirect()->route('admin.events.index')
        : redirect()->route('events.index');
})->middleware(['auth', 'verified'])->name('dashboard');


/* ---------- Authenticated user area ---------- */
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Events (list + show only)
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

    // Bookings (users can book)
    Route::post('/events/{event}/book', [BookingController::class, 'store'])->name('bookings.store');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/top5-last30', [ReportController::class, 'top5EventsLast30'])->name('top5');
        Route::get('/power-users-last-month', [ReportController::class, 'usersBookedMoreThan3LastMonth'])->name('power_users');
        Route::get('/occupancy', [ReportController::class, 'occupancy'])->name('occupancy');
    });
});

/* ---------- Admin-only area (create events) ---------- */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
});

require __DIR__ . '/auth.php';

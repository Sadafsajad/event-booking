<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function store(Request $r, Event $event)
    {
        $data = $r->validate(['qty' => 'nullable|integer|min:1']);
        $qty = $data['qty'] ?? 1;
        $user = Auth::user();

        $booking = DB::transaction(function () use ($event, $qty, $user) {
            // Lock the event row to avoid race conditions
            $e = Event::whereKey($event->id)->lockForUpdate()->first();

            // If user already booked this event (unique user_id,event_id), give friendly error
            $alreadyUser = Booking::where('user_id', $user->id)
                ->where('event_id', $e->id)
                ->exists();
            if ($alreadyUser) {
                throw ValidationException::withMessages([
                    'qty' => 'You have already booked this event.',
                ]);
            }

            // Check current seats
            $booked = (int) Booking::where('event_id', $e->id)->sum('qty');
            $remaining = max(0, $e->capacity - $booked);
            if ($remaining <= 0 || $qty > $remaining) {
                throw ValidationException::withMessages([
                    'qty' => "Only {$remaining} seat(s) left.",
                ]);
            }

            return Booking::create([
                'user_id' => $user->id,
                'event_id' => $e->id,
                'qty' => $qty,
            ]);
        });

        // Invalidate cached event lists (your versioned cache)
        if (!Cache::has('events:version')) {
            Cache::forever('events:version', 1);
        } else {
            Cache::increment('events:version');
        }

        // Simple email (local dev: set MAIL_MAILER=log to see it in storage/logs/laravel.log)
        Mail::to($user->email)->send(new BookingConfirmed($booking));

        // Web vs API response
        if ($r->expectsJson() || $r->wantsJson()) {
            return response()->json(['message' => 'Booked!', 'booking' => $booking], 201);
        }

        return back()->with('status', 'Booking confirmed!');
    }
}

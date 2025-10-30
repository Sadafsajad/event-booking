<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Event;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Log;

class BookingController extends Controller
{
    public function store(Request $r, Event $event)
    {
        $data = $r->validate(['qty' => 'nullable|integer|min:1']);
        $qty = $data['qty'] ?? 1;

        // temporarily disable auth dependency while testing
        // $user = Auth::user();
        $user = (object) [
            'id' => 1,
            'email' => 'sadafsajad451@gmail.com'
        ];

        $booking = DB::transaction(function () use ($event, $qty, $user) {
            // Lock event to avoid race conditions
            $e = Event::whereKey($event->id)->lockForUpdate()->first();


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

        // Invalidate cached events version
        /*if (!Cache::has('events:version')) {
            Cache::forever('events:version', 1);
        } else {
            Cache::increment('events:version');
        }

        // Send booking confirmation email (commented for local testing)
        Mail::to($user->email)->send(new BookingConfirmed($booking));*/
        Mail::to($user->email)->queue(new BookingConfirmed($booking));
        return response()->json(['message' => 'Booked!', 'booking' => $booking], 201);
    }
}

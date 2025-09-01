<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(Request $r, Event $event)
    {
        $data = $r->validate(['qty' => 'nullable|integer|min:1']);
        $qty = $data['qty'] ?? 1;
        $user = Auth::user();

        $booking = DB::transaction(function () use ($event, $qty, $user) {
            // lock the row to avoid race conditions
            $e = Event::whereKey($event->id)->lockForUpdate()->first();

            $already = (int) $e->bookings()->sum('qty');
            if ($already + $qty > $e->capacity) {
                throw ValidationException::withMessages([
                    'qty' => 'Event is full or not enough seats.'
                ]);
            }

            // prevent duplicate booking by same user
            $booking = Booking::firstOrCreate(
                ['user_id' => $user->id, 'event_id' => $e->id],
                ['qty' => $qty]
            );

            // if exists, you might want to increase qty instead:
            // $booking->increment('qty', $qty);

            return $booking;
        });

        // Bonus: queue a confirmation email
        \Mail::to($user->email)->queue(new BookingConfirmed($booking));

        return ['message' => 'Booked!', 'booking' => $booking];
    }
}

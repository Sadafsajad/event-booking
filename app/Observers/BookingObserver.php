<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        $event = Event::find($booking->event_id);
        if ($event) {
            // Just log how many seats are left
            $booked = (int) $event->bookings()->sum('qty');
            $left = max($event->capacity - $booked, 0);

            // Log::info('✅ Booking created', [
            //     'booking_id' => $booking->id,
            //     'event_id' => $event->id,
            //     'booked_qty' => $booking->qty,
            //     'remaining' => $left,
            // ]);
        }

        // Invalidate cache so frontend sees updated values
        if (!Cache::has('events:version')) {
            Cache::forever('events:version', 1);
        } else {
            Cache::increment('events:version');
        }
    }

    public function deleted(Booking $booking): void
    {
        Log::warning('🚫 Booking cancelled', ['id' => $booking->id]);
    }
}

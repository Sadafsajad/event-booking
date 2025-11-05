<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    /**
     * Given an array of event ids, return [$bookedMap, $mineMap]
     * where each is a collection keyed by event_id.
     */
    public function getBookingStats(array $eventIds): array
    {
        if (empty($eventIds)) {
            return [collect(), collect()];
        }

        $bookedMap = Booking::selectRaw('event_id, SUM(qty) as booked')
            ->whereIn('event_id', $eventIds)
            ->groupBy('event_id')
            ->pluck('booked', 'event_id');

        $mineMap = Booking::where('user_id', Auth::id())
            ->whereIn('event_id', $eventIds)
            ->pluck('qty', 'event_id');

        return [$bookedMap, $mineMap];
    }
}

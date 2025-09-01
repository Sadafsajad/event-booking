<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function top5EventsLast30()
    {
        $since = now()->subDays(30);
        return Event::withCount([
            'bookings as bookings_last_30' => function ($q) use ($since) {
                $q->where('created_at', '>=', $since);
            }
        ])
            ->orderByDesc('bookings_last_30')
            ->take(5)
            ->get();
    }

    public function usersBookedMoreThan3LastMonth()
    {
        $start = now()->subMonth()->startOfMonth();
        $end = now()->subMonth()->endOfMonth();

        return User::withCount([
            'bookings' => function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            }
        ])
            ->having('bookings_count', '>', 3)
            ->get();
    }

    public function occupancyPerEvent()
    {
        return Event::withCount('bookings')->get()->map(function ($e) {
            $pct = $e->capacity ? round(($e->bookings_count / $e->capacity) * 100, 2) : 0;
            return [
                'id' => $e->id,
                'title' => $e->title,
                'capacity' => $e->capacity,
                'bookings' => $e->bookings_count,
                'occupancy_percent' => $pct,
            ];
        });
    }
}

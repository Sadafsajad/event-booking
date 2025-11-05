<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    public function top5()
    {
        $from = Carbon::now()->subDays(30);

        return DB::table('events as e')
            ->leftJoin('bookings as b', 'b.event_id', '=', 'e.id')
            ->selectRaw(
                'e.id, e.title, e.venue, e.capacity, e.event_at,
                COALESCE(SUM(CASE WHEN b.created_at >= ? THEN b.qty ELSE 0 END),0) AS booked_last30',
                [$from]
            )
            ->groupBy('e.id', 'e.title', 'e.venue', 'e.capacity', 'e.event_at')
            ->orderByDesc('booked_last30')
            ->limit(5)
            ->get();
    }

    public function powerUsers()
    {
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::now()->subMonth()->endOfMonth();

        return DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->whereBetween('b.created_at', [$start, $end])
            ->selectRaw('u.id, u.name, u.email, COUNT(DISTINCT b.event_id) as events_count')
            ->groupBy('u.id', 'u.name', 'u.email')
            ->havingRaw('COUNT(DISTINCT b.event_id) > 3')
            ->orderByDesc('events_count')
            ->get();
    }

    public function occupancy()
    {
        return DB::table('events as e')
            ->leftJoin('bookings as b', 'b.event_id', '=', 'e.id')
            ->selectRaw(
                'e.id, e.title, e.venue, e.capacity, e.event_at,
                COALESCE(SUM(b.qty),0) as booked,
                CASE WHEN e.capacity > 0
                     THEN ROUND(COALESCE(SUM(b.qty),0) * 100.0 / e.capacity, 2)
                     ELSE 0 END as occupancy_percent'
            )
            ->groupBy('e.id', 'e.title', 'e.venue', 'e.capacity', 'e.event_at')
            ->orderByDesc('occupancy_percent')
            ->get();
    }
}

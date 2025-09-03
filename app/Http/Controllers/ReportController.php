<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Admin dashboard (3 tables in one page)
    public function dashboard()
    {
        $top5 = $this->queryTop5Last30();
        $powerUsers = $this->queryPowerUsersLastMonth();
        $occupancy = $this->queryOccupancy();

        return view('admin.reports.dashboard', compact('top5', 'powerUsers', 'occupancy'));
    }

    // 1) Top 5 events by bookings in last 30 days
    public function top5EventsLast30()
    {
        $rows = $this->queryTop5Last30();
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json($rows);
        }
        return view('admin.reports.top5', ['rows' => $rows]);
    }

    // 2) Users who booked >3 events last month (DISTINCT events)
    public function usersBookedMoreThan3LastMonth()
    {
        $rows = $this->queryPowerUsersLastMonth();
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json($rows);
        }
        return view('admin.reports.power_users', ['rows' => $rows]);
    }

    // 3) % occupancy for each event
    public function occupancyPerEvent()
    {
        $rows = $this->queryOccupancy();
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json($rows);
        }
        return view('admin.reports.occupancy', ['rows' => $rows]);
    }

    /* ----------------- Raw queries (reuse) ----------------- */

    private function queryTop5Last30()
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

    private function queryPowerUsersLastMonth()
    {
        // previous calendar month
        $start = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $end = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        return DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->whereBetween('b.created_at', [$start, $end])
            ->selectRaw('u.id, u.name, u.email, COUNT(DISTINCT b.event_id) as events_count')
            ->groupBy('u.id', 'u.name', 'u.email')
            ->havingRaw('COUNT(DISTINCT b.event_id) > 3')
            ->orderByDesc('events_count')
            ->get();
    }

    private function queryOccupancy()
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

<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    public function index(Request $r)
    {
        $q = Event::query();

        if ($term = $r->query('q')) {
            $q->where(function ($w) use ($term) {
                $w->where('title', 'like', "%$term%")
                    ->orWhere('venue', 'like', "%$term%");
            });
        }
        if ($from = $r->query('from'))
            $q->where('event_at', '>=', $from);
        if ($to = $r->query('to'))
            $q->where('event_at', '<=', $to);

        $q->orderBy('event_at', 'asc');

        // Bonus: cache list by full URL (filters included) for 60s
        $key = 'events:' . md5($r->fullUrl());
        return Cache::remember($key, 60, fn() => $q->paginate(10));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'event_at' => 'required|date',
            'description' => 'nullable|string',
        ]);
        return Event::create($data);
    }

    public function show(Event $event)
    {
        // include occupancy %
        $booked = $event->bookedQty();
        return [
            'event' => $event,
            'booked' => $booked,
            'occupancy' => $event->capacity ? round(($booked / $event->capacity) * 100, 2) : 0,
        ];
    }
}

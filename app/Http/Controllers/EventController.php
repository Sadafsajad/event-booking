<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class EventController extends Controller
{
    private int $perPage = 10;

    public function create()
    {
        return view('events.create');
    }

    private function listCacheKey(Request $r): string
    {
        $version = Cache::get('events:version', 1);
        $payload = [
            'q' => $r->query('q'),
            'from' => $r->query('from'),
            'to' => $r->query('to'),
            'page' => (int) $r->query('page', 1),
            'per' => $this->perPage,
        ];
        return 'events:v' . $version . ':' . md5(json_encode($payload));
    }

    private function bumpEventsVersion(): void
    {
        if (!Cache::has('events:version')) {
            Cache::forever('events:version', 1);
        } else {
            Cache::increment('events:version');
        }
    }

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

        $key = $this->listCacheKey($r);
        $data = Cache::remember(
            $key,
            now()->addMinutes(5),
            fn() =>
            $q->paginate($this->perPage)->toArray()
        );

        if ($r->expectsJson() || $r->wantsJson()) {
            return response()->json($data);
        }

        // Rebuild paginator for Blade
        $items = collect($data['data'])->map(fn($row) => (object) $row);
        $events = new LengthAwarePaginator(
            $items,
            $data['total'],
            $data['per_page'],
            $data['current_page'],
            ['path' => $r->url(), 'query' => $r->query()]
        );

        // --- NEW: get booked totals for visible rows (one query) ---
        $ids = $items->pluck('id')->all();
        $bookedMap = collect();
        $mineMap = collect();

        if (!empty($ids)) {
            $bookedMap = Booking::selectRaw('event_id, SUM(qty) as booked')
                ->whereIn('event_id', $ids)
                ->groupBy('event_id')
                ->pluck('booked', 'event_id');

            if (auth()->check()) {
                $mineMap = Booking::where('user_id', auth()->id())
                    ->whereIn('event_id', $ids)
                    ->pluck('qty', 'event_id');
            }
        }

        return view('events.index', compact('events', 'bookedMap', 'mineMap'));
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

        if (str_contains($data['event_at'], 'T')) {
            $data['event_at'] = str_replace('T', ' ', $data['event_at']);
        }

        $event = Event::create($data);
        $this->bumpEventsVersion();

        return $r->expectsJson()
            ? response()->json($event, 201)
            : redirect()->route('admin.events.index')->with('status', 'Event created.');
    }

    public function show(Event $event)
    {
        $booked = $event->bookedQty();
        return [
            'event' => $event,
            'booked' => $booked,
            'occupancy' => $event->capacity ? round(($booked / $event->capacity) * 100, 2) : 0,
        ];
    }

    public function update(Request $r, Event $event)
    {
        $data = $r->validate([
            'title' => 'sometimes|required|string|max:255',
            'venue' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1',
            'event_at' => 'sometimes|required|date',
            'description' => 'nullable|string',
        ]);

        $event->update($data);
        $this->bumpEventsVersion();

        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        $this->bumpEventsVersion();

        return response()->json(['message' => 'Deleted']);
    }
}

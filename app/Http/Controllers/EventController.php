<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
class EventController extends Controller
{
    private int $perPage = 10;

    /** Build a cache key that changes when:
     *   - query params change (q/from/to/page)
     *   - the global "events cache version" changes (bumped on create/update/delete)
     */
    public function create()
    {
        // simple admin form
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

    /** Bump version to invalidate all cached event lists in one go */
    private function bumpEventsVersion(): void
    {
        // increment returns false if key missing on some drivers; set a default then increment
        if (!Cache::has('events:version')) {
            Cache::forever('events:version', 1);
        } else {
            Cache::increment('events:version');
        }
    }

    // ------- PUBLIC LIST (search / filter / paginate) -------
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
        $data = Cache::remember($key, now()->addMinutes(5), function () use ($q) {
            return $q->paginate($this->perPage)->toArray(); // <= your original
        });

        // If API/JSON request -> return JSON (unchanged behavior)
        if ($r->expectsJson() || $r->wantsJson()) {
            return response()->json($data);
        }

        // Browser request -> rebuild a paginator for Blade
        $items = collect($data['data'])->map(fn($row) => (object) $row); // property access in Blade
        $events = new LengthAwarePaginator(
            $items,
            $data['total'],
            $data['per_page'],
            $data['current_page'],
            ['path' => $r->url(), 'query' => $r->query()]
        );

        return view('events.index', compact('events'));
    }

    // ------- ADMIN: CREATE EVENT -------
    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'event_at' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // support datetime-local
        if (str_contains($data['event_at'], 'T')) {
            $data['event_at'] = str_replace('T', ' ', $data['event_at']);
        }

        $event = Event::create($data);
        $this->bumpEventsVersion();

        if ($r->expectsJson() || $r->wantsJson()) {
            return response()->json($event, 201);
        }
        return redirect()->route('admin.events.index')->with('status', 'Event created.');
    }

    // ------- SHOW ONE EVENT (optionally cache this too) -------
    public function show(Event $event)
    {
        $booked = $event->bookedQty();
        return [
            'event' => $event,
            'booked' => $booked,
            'occupancy' => $event->capacity ? round(($booked / $event->capacity) * 100, 2) : 0,
        ];
    }

    // ------- (Optional) ADMIN update/delete -> also bump version -------
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

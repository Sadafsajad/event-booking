<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
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

    public function index(Request $request)
    {
        $query = Event::query();

        // 🔍 Apply filters
        if ($searchTerm = $request->query('q')) {
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('venue', 'like', "%{$searchTerm}%");
            });
        }

        if ($fromDate = $request->query('from')) {
            $query->where('event_at', '>=', $fromDate);
        }

        if ($toDate = $request->query('to')) {
            $query->where('event_at', '<=', $toDate);
        }

        // 📊 Sorting
        $sortBy = $request->query('sort', 'id');
        $sortOrder = $request->query('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 📄 Pagination
        $perPage = $request->query('per_page', 10);
        $events = $query->paginate($perPage);

        $eventIds = $events->pluck('id')->all();

        $bookedMap = $mineMap = collect();

        if (!empty($eventIds)) {
            // Total booked per event
            $bookedMap = Booking::selectRaw('event_id, SUM(qty) as booked')
                ->whereIn('event_id', $eventIds)
                ->groupBy('event_id')
                ->pluck('booked', 'event_id');

            // Simulated user for now
            $userId = Auth::id();

            // User's own bookings
            $mineMap = Booking::where('user_id', $userId)
                ->whereIn('event_id', $eventIds)
                ->pluck('qty', 'event_id');
        }

        $events->getCollection()->transform(function ($event) use ($bookedMap, $mineMap) {
            $bookedCount = (int) ($bookedMap[$event->id] ?? 0);
            $userBookedCount = (int) ($mineMap[$event->id] ?? 0);

            $event->booked = $bookedCount;
            $event->mine_qty = $userBookedCount;
            $event->left = max($event->capacity - $bookedCount, 0);

            return $event;
        });

        return response()->json($events);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Map `date` → `event_at`
        $data['event_at'] = $data['date'];
        unset($data['date']);

        if (str_contains($data['event_at'], 'T')) {
            $data['event_at'] = str_replace('T', ' ', $data['event_at']);
        }

        $event = Event::create($data);
        // $this->bumpEventsVersion();

        return response()->json($event, 201);
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
        // $this->bumpEventsVersion();

        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        // $this->bumpEventsVersion();

        return response()->json(['message' => 'Deleted']);
    }
}

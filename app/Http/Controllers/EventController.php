<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\EventService;
use App\Services\BookingService;

class EventController extends Controller
{
    private int $perPage = 10;

    private EventService $events;
    private BookingService $bookings;

    public function __construct(EventService $events, BookingService $bookings)
    {
        $this->events = $events;
        $this->bookings = $bookings;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'from', 'to', 'sort', 'order', 'per_page']);

        $events = $this->events->listEvents($filters);

        $eventIds = $events->pluck('id')->all();

        [$bookedMap, $mineMap] = $this->bookings->getBookingStats($eventIds);

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

        $event = $this->events->createEvent($data);

        return response()->json($event, 201);
    }

    public function edit(Event $event)
    {
        return response()->json($this->events->getEventForEdit($event));
    }

    public function update(Request $r, Event $event)
    {
        $data = $r->validate([
            'title' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $event = $this->events->updateEvent($event, $data);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

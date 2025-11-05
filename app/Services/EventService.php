<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Pagination\LengthAwarePaginator;

class EventService
{
    /**
     * List events with optional filters and sorting.
     * Returns a LengthAwarePaginator.
     *
     * $filters keys: q, from, to, sort, order, per_page
     */
    public function listEvents(array $filters = []): LengthAwarePaginator
    {
        $query = Event::query();

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('venue', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['from'])) {
            $query->where('event_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('event_at', '<=', $filters['to']);
        }

        $sort = $filters['sort'] ?? 'id';
        $order = $filters['order'] ?? 'desc';
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query->orderBy($sort, $order);

        return $query->paginate($perPage);
    }

    /**
     * Create a new event. Expects 'date' in payload (maps to event_at).
     */
    public function createEvent(array $data): Event
    {
        $data['event_at'] = $this->normalizeDateKey($data['date'] ?? $data['event_at'] ?? null);
        unset($data['date']);

        return Event::create($data);
    }

    /**
     * Update an existing event. Expects 'date' key (maps to event_at).
     */
    public function updateEvent(Event $event, array $data): Event
    {
        if (isset($data['date'])) {
            $data['event_at'] = $this->normalizeDateKey($data['date']);
            unset($data['date']);
        }

        $event->update($data);

        return $event;
    }

    private function normalizeDateKey($date)
    {
        if ($date === null)
            return null;
        return str_contains($date, 'T') ? str_replace('T', ' ', $date) : $date;
    }

    /**
     * Return event payload suitable for editing form (you can shape it here).
     */
    public function getEventForEdit(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'venue' => $event->venue,
            'capacity' => $event->capacity,
            'event_at' => $event->event_at,
            'description' => $event->description,
        ];
    }
}

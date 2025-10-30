<?php

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    public function created(Event $event): void
    {
        // Invalidate cache
        if (!Cache::has('events:version')) {
            Cache::forever('events:version', 1);
        } else {
            Cache::increment('events:version');
        }

        // Log it
        Log::info('🎉 New event created', [
            'id' => $event->id,
            'title' => $event->title,
            'venue' => $event->venue,
            'capacity' => $event->capacity,
        ]);
    }

    public function updated(Event $event): void
    {
        // Example: log updates
        Log::info('✏️ Event updated', [
            'id' => $event->id,
            'changes' => $event->getChanges(),
        ]);
    }

    public function deleted(Event $event): void
    {
        Log::warning('🗑️ Event deleted', ['id' => $event->id]);
    }
}

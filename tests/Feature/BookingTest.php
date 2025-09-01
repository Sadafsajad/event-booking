<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_cannot_book_when_full()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $event = \App\Models\Event::create([
            'title' => 'Test',
            'venue' => 'Hall A',
            'capacity' => 1,
            'event_at' => now()->addDay(),
        ]);

        // first booking ok
        $this->post("/events/{$event->id}/book", ['qty' => 1])->assertStatus(200);

        // second booking should fail
        $this->post("/events/{$event->id}/book", ['qty' => 1])->assertSessionHasErrors();
    }

}

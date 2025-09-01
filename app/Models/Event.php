<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title', 'venue', 'capacity', 'event_at', 'description'];
    protected $casts = ['event_at' => 'datetime'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function bookedQty(): int
    {
        return (int) $this->bookings()->sum('qty');
    }
}


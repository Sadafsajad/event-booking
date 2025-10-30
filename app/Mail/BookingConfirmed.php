<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('Your Event Booking Confirmation')
            ->markdown('emails.booking.confirmed')
            ->with([
                'event' => $this->booking->event->title,
                'venue' => $this->booking->event->venue,
                'qty' => $this->booking->qty,
                'date' => $this->booking->event->event_at,
            ]);
    }
}

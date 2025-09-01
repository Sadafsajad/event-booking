<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public \App\Models\Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your booking is confirmed');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.booking.confirmed', with: [
            'user' => $this->booking->user,
            'event' => $this->booking->event,
            'qty' => $this->booking->qty,
        ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

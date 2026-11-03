<?php

namespace App\Mail;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        $night = $this->booking->night;

        return new Envelope(
            subject: $this->booking->status === BookingStatus::Confirmed
                ? "You're playing at {$night->title}"
                : "You're on the waitlist for {$night->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.booking-received',
            with: [
                'night' => $this->booking->night,
                'djSlot' => $this->booking->slot,
                'cancelUrl' => $this->booking->cancelUrl(),
            ],
        );
    }
}

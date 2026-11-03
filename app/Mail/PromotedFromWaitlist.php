<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromotedFromWaitlist extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "A slot opened up: you're playing at {$this->booking->night->title}");
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.promoted-from-waitlist',
            with: [
                'night' => $this->booking->night,
                'djSlot' => $this->booking->slot,
                'cancelUrl' => $this->booking->cancelUrl(),
            ],
        );
    }
}

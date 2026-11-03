<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Mail\PromotedFromWaitlist;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Cancels a booking (by the DJ or the host). A freed slot goes straight to the
 * first DJ on the waitlist, who is told by email.
 */
class CancelBooking
{
    public function __construct(private readonly SlotClaimer $slots) {}

    /**
     * @return Booking|null the DJ promoted from the waitlist, if any
     */
    public function handle(Booking $booking): ?Booking
    {
        if ($booking->status === BookingStatus::Cancelled) {
            return null;
        }

        $promoted = DB::transaction(function () use ($booking): ?Booking {
            $booking->forceFill([
                'status' => BookingStatus::Cancelled,
                'cancelled_at' => now(),
            ])->save();

            $freed = $this->slots->release($booking);

            if ($freed === null || ! $booking->night->isOpenForBookings()) {
                return null;
            }

            $next = $booking->night->waitlist()->first();

            if ($next === null || ! $this->slots->claim($freed, $next)) {
                return null;
            }

            $next->update(['status' => BookingStatus::Confirmed]);

            return $next;
        });

        if ($promoted !== null) {
            Mail::to($promoted->email)->send(new PromotedFromWaitlist($promoted));
        }

        return $promoted;
    }
}

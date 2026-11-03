<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Exceptions\BookingNotPossible;
use App\Mail\BookingReceived;
use App\Models\Booking;
use App\Models\Night;
use App\Models\Slot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * A DJ asks to play. They get the slot they picked, the earliest free slot,
 * or a place on the waitlist when the night is full.
 */
class RequestSlot
{
    public function __construct(private readonly SlotClaimer $slots) {}

    /**
     * @param  array{dj_name: string, email: string, genre?: string|null, mix_url?: string|null}  $dj
     *
     * @throws BookingNotPossible
     */
    public function handle(Night $night, array $dj, ?Slot $preferred = null): Booking
    {
        if (! $night->isOpenForBookings()) {
            throw BookingNotPossible::nightClosed();
        }

        $booking = DB::transaction(function () use ($night, $dj, $preferred): Booking {
            $email = Str::lower(trim($dj['email']));

            $alreadyBooked = $night->bookings()
                ->where('email', $email)
                ->where('status', '!=', BookingStatus::Cancelled)
                ->exists();

            if ($alreadyBooked) {
                throw BookingNotPossible::alreadyBooked();
            }

            $booking = $night->bookings()->create([
                'dj_name' => trim($dj['dj_name']),
                'email' => $email,
                'genre' => $dj['genre'] ?? null,
                'mix_url' => $dj['mix_url'] ?? null,
                'status' => BookingStatus::Waitlisted,
            ]);

            $slot = $preferred
                ? $this->claimPreferred($preferred, $booking)
                : $this->slots->claimFirstFree($night, $booking);

            if ($slot !== null) {
                $booking->update(['status' => BookingStatus::Confirmed]);
            }

            return $booking;
        });

        Mail::to($booking->email)->send(new BookingReceived($booking));

        return $booking;
    }

    private function claimPreferred(Slot $slot, Booking $booking): Slot
    {
        if (! $this->slots->claim($slot, $booking)) {
            // Roll back the booking so the DJ can simply pick another slot.
            throw BookingNotPossible::slotTaken();
        }

        return $slot;
    }
}

<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\Night;
use App\Models\Slot;

/**
 * Assigns bookings to slots with a single conditional UPDATE.
 *
 * "UPDATE slots SET booking_id = ? WHERE id = ? AND booking_id IS NULL" is
 * atomic in every database Laravel supports, so when two DJs race for the same
 * slot exactly one update affects a row and the other one simply gets false.
 * No pessimistic locks are needed, and the unique index on slots.booking_id is
 * a second safety net.
 */
class SlotClaimer
{
    public function claim(Slot $slot, Booking $booking): bool
    {
        $claimed = Slot::query()
            ->whereKey($slot->id)
            ->whereNull('booking_id')
            ->update(['booking_id' => $booking->id]) === 1;

        if ($claimed) {
            $slot->booking_id = $booking->id;
        }

        return $claimed;
    }

    /**
     * Claims the earliest free slot of the night, retrying if another request
     * wins the race for a slot between our read and our write.
     */
    public function claimFirstFree(Night $night, Booking $booking): ?Slot
    {
        while ($slot = $this->firstFree($night)) {
            if ($this->claim($slot, $booking)) {
                return $slot;
            }
        }

        return null;
    }

    public function release(Booking $booking): ?Slot
    {
        $slot = Slot::query()->where('booking_id', $booking->id)->first();

        if ($slot === null) {
            return null;
        }

        Slot::query()->whereKey($slot->id)->update(['booking_id' => null]);
        $slot->booking_id = null;

        return $slot;
    }

    private function firstFree(Night $night): ?Slot
    {
        return $night->slots()->whereNull('booking_id')->first();
    }
}

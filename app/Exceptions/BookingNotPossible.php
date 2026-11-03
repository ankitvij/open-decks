<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business rule stopped the booking. The message is safe to show to the DJ.
 */
class BookingNotPossible extends RuntimeException
{
    public static function nightClosed(): self
    {
        return new self('Bookings for this night are closed.');
    }

    public static function alreadyBooked(): self
    {
        return new self('This email already has a slot or waitlist spot for this night.');
    }

    public static function slotTaken(): self
    {
        return new self('Sorry, someone just took that slot. Please pick another one.');
    }
}

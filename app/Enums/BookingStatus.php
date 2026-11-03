<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Confirmed = 'confirmed';
    case Waitlisted = 'waitlisted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Confirmed',
            self::Waitlisted => 'On the waitlist',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isActive(): bool
    {
        return $this !== self::Cancelled;
    }
}

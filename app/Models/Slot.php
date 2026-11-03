<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $night_id
 * @property int $position
 * @property Carbon $starts_at
 * @property int|null $booking_id
 */
class Slot extends Model
{
    protected $fillable = ['position', 'starts_at'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<Night, $this> */
    public function night(): BelongsTo
    {
        return $this->belongsTo(Night::class);
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isFree(): bool
    {
        return $this->booking_id === null;
    }

    public function endsAt(): Carbon
    {
        return $this->starts_at->copy()->addMinutes($this->night->slot_minutes);
    }
}

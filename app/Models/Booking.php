<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * A DJ's request to play: either confirmed in a slot, waiting, or cancelled.
 *
 * @property int $id
 * @property int $night_id
 * @property string $dj_name
 * @property string $email
 * @property string|null $genre
 * @property string|null $mix_url
 * @property BookingStatus $status
 * @property Carbon|null $cancelled_at
 * @property Carbon $created_at
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = ['dj_name', 'email', 'genre', 'mix_url', 'status'];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Night, $this> */
    public function night(): BelongsTo
    {
        return $this->belongsTo(Night::class);
    }

    /** @return HasOne<Slot, $this> */
    public function slot(): HasOne
    {
        return $this->hasOne(Slot::class);
    }

    /**
     * Signed link that lets a DJ cancel without an account. It expires when the
     * night starts, so old emails cannot free slots during the event.
     */
    public function cancelUrl(): string
    {
        return URL::temporarySignedRoute(
            'bookings.cancel',
            $this->night->starts_at,
            ['booking' => $this->id],
        );
    }

    /** Signed form action used by the cancel confirmation page. */
    public function cancelActionUrl(): string
    {
        return URL::temporarySignedRoute(
            'bookings.cancel.store',
            $this->night->starts_at,
            ['booking' => $this->id],
        );
    }
}

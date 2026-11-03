<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\NightFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An open-decks night with a fixed number of equally long DJ slots.
 *
 * @property int $id
 * @property int $host_id
 * @property string $title
 * @property string $slug
 * @property string $venue
 * @property Carbon $starts_at
 * @property int $slot_minutes
 * @property int $slot_count
 * @property string|null $description
 * @property bool $is_published
 */
class Night extends Model
{
    /** @use HasFactory<NightFactory> */
    use HasFactory;

    /** Mirrors the column default so fresh models behave like stored ones. */
    protected $attributes = [
        'is_published' => true,
    ];

    protected $fillable = [
        'title',
        'slug',
        'venue',
        'starts_at',
        'slot_minutes',
        'slot_count',
        'description',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'slot_minutes' => 'integer',
            'slot_count' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<User, $this> */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /** @return HasMany<Slot, $this> */
    public function slots(): HasMany
    {
        // chaperone() sets slot->night without extra queries (no N+1 in lineups).
        return $this->hasMany(Slot::class)->orderBy('position')->chaperone('night');
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** @return HasMany<Booking, $this> */
    public function waitlist(): HasMany
    {
        return $this->bookings()
            ->where('status', BookingStatus::Waitlisted)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * @param  Builder<Night>  $query
     * @return Builder<Night>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at');
    }

    public function endsAt(): Carbon
    {
        return $this->starts_at->copy()->addMinutes($this->slot_minutes * $this->slot_count);
    }

    /** Bookings close once the night has started. */
    public function isOpenForBookings(): bool
    {
        return $this->is_published && $this->starts_at->isFuture();
    }
}

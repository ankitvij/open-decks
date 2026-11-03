<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Night;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Raw bookings without slot assignment. Tests use App\Actions\RequestSlot
 * when they need realistic slot/waitlist state.
 *
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'night_id' => Night::factory(),
            'dj_name' => 'DJ '.fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'genre' => fake()->randomElement(['Techno', 'Afro house', 'Disco', 'Drum & bass']),
            'mix_url' => null,
            'status' => BookingStatus::Waitlisted,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Night;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Creates nights *with* their slots, mirroring App\Actions\CreateNight.
 *
 * @extends Factory<Night>
 */
class NightFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->randomElement(['Wednesday Open Decks', 'Vinyl Night', 'Afro House Session', 'Techno Tuesday']);

        return [
            'host_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'venue' => fake()->randomElement(['Sensorium', 'Club Panke', 'Hoppetosse']).', Berlin',
            'starts_at' => now()->addWeek()->setTime(20, 0),
            'slot_minutes' => 45,
            'slot_count' => 4,
            'is_published' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Night $night): void {
            $night->slots()->createMany(
                collect(range(1, $night->slot_count))->map(fn (int $position) => [
                    'position' => $position,
                    'starts_at' => $night->starts_at->copy()->addMinutes(($position - 1) * $night->slot_minutes),
                ])->all()
            );
        });
    }

    public function slots(int $count): static
    {
        return $this->state(['slot_count' => $count]);
    }

    public function past(): static
    {
        return $this->state(['starts_at' => now()->subDay()->setTime(20, 0)]);
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}

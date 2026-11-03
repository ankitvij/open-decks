<?php

namespace App\Actions;

use App\Models\Night;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates a night and generates its back-to-back slots.
 */
class CreateNight
{
    /**
     * @param  array{title: string, venue: string, starts_at: string, slot_minutes: int|string, slot_count: int|string, description?: string|null}  $data
     */
    public function handle(User $host, array $data): Night
    {
        return DB::transaction(function () use ($host, $data): Night {
            $startsAt = Carbon::parse($data['starts_at']);

            /** @var Night $night */
            $night = $host->nights()->create([
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title'], $startsAt),
                'venue' => $data['venue'],
                'starts_at' => $startsAt,
                'slot_minutes' => (int) $data['slot_minutes'],
                'slot_count' => (int) $data['slot_count'],
                'description' => $data['description'] ?? null,
            ]);

            $night->slots()->createMany(
                collect(range(1, $night->slot_count))->map(fn (int $position) => [
                    'position' => $position,
                    'starts_at' => $startsAt->copy()->addMinutes(($position - 1) * $night->slot_minutes),
                ])->all()
            );

            return $night;
        });
    }

    private function uniqueSlug(string $title, Carbon $startsAt): string
    {
        $base = Str::slug($title.' '.$startsAt->format('Y-m-d'));
        $slug = $base;
        $suffix = 2;

        while (Night::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}

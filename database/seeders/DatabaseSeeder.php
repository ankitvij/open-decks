<?php

namespace Database\Seeders;

use App\Actions\CreateNight;
use App\Actions\RequestSlot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data: one host, one night that is almost full and one that is open.
     * Login: host@example.com / password
     */
    public function run(): void
    {
        Mail::fake();

        $host = User::factory()->create([
            'name' => 'Demo Host',
            'email' => 'host@example.com',
        ]);

        $createNight = app(CreateNight::class);
        $requestSlot = app(RequestSlot::class);

        $busy = $createNight->handle($host, [
            'title' => 'Wednesday Open Decks',
            'venue' => 'Sensorium, Berlin',
            'starts_at' => now()->next('Wednesday')->setTime(20, 0)->toDateTimeString(),
            'slot_minutes' => 45,
            'slot_count' => 4,
            'description' => "Bring your USB or vinyl. CDJ-3000s and a DJM-900 are set up.\nFriendly crowd, all genres welcome.",
        ]);

        foreach ([['Mira', 'Afro house'], ['K-Lo', 'Techno'], ['Nachtfalter', 'Minimal']] as [$name, $genre]) {
            $requestSlot->handle($busy, [
                'dj_name' => $name,
                'email' => strtolower($name).'@example.com',
                'genre' => $genre,
            ]);
        }

        $createNight->handle($host, [
            'title' => 'Vinyl Only Sunday',
            'venue' => 'Club Panke, Berlin',
            'starts_at' => now()->next('Sunday')->setTime(18, 0)->toDateTimeString(),
            'slot_minutes' => 60,
            'slot_count' => 5,
            'description' => 'Two Technics 1210s. Records only.',
        ]);
    }
}

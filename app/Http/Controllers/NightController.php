<?php

namespace App\Http\Controllers;

use App\Models\Night;
use Illuminate\View\View;

class NightController extends Controller
{
    public function index(): View
    {
        $nights = Night::query()
            ->upcoming()
            ->withCount([
                'slots as free_slots_count' => fn ($query) => $query->whereNull('booking_id'),
            ])
            ->get();

        return view('nights.index', ['nights' => $nights]);
    }

    public function show(Night $night): View
    {
        abort_unless($night->is_published, 404);

        $night->load(['slots.booking']);

        return view('nights.show', [
            'night' => $night,
            'freeSlots' => $night->slots->filter->isFree(),
            'waitlistCount' => $night->waitlist()->count(),
        ]);
    }
}

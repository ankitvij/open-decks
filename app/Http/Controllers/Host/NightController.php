<?php

namespace App\Http\Controllers\Host;

use App\Actions\CancelBooking;
use App\Actions\CreateNight;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNightRequest;
use App\Models\Booking;
use App\Models\Night;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NightController extends Controller
{
    public function index(Request $request): View
    {
        $nights = $request->user()->nights()
            ->withCount([
                'slots',
                'slots as booked_slots_count' => fn ($query) => $query->whereNotNull('booking_id'),
                'bookings as waitlist_count' => fn ($query) => $query->where('status', BookingStatus::Waitlisted),
            ])
            ->orderByDesc('starts_at')
            ->get();

        return view('host.nights.index', ['nights' => $nights]);
    }

    public function create(): View
    {
        return view('host.nights.create');
    }

    public function store(StoreNightRequest $request, CreateNight $createNight): RedirectResponse
    {
        $night = $createNight->handle($request->user(), $request->validated());

        return to_route('host.nights.show', $night)->with('status', 'Night created with '.$night->slot_count.' slots.');
    }

    public function show(Night $night): View
    {
        Gate::authorize('manage', $night);

        $night->load(['slots.booking']);

        return view('host.nights.show', [
            'night' => $night,
            'waitlist' => $night->waitlist()->get(),
        ]);
    }

    /** The host removes a DJ (no-show, double booking, ...). The waitlist moves up. */
    public function removeBooking(Night $night, Booking $booking, CancelBooking $cancelBooking): RedirectResponse
    {
        Gate::authorize('manage', $night);
        abort_unless($booking->night_id === $night->id, 404);

        $promoted = $cancelBooking->handle($booking);

        $message = "Removed {$booking->dj_name}.";
        if ($promoted !== null) {
            $message .= " {$promoted->dj_name} moved up from the waitlist and was notified.";
        }

        return to_route('host.nights.show', $night)->with('status', $message);
    }
}

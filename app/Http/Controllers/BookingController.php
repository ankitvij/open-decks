<?php

namespace App\Http\Controllers;

use App\Actions\CancelBooking;
use App\Actions\RequestSlot;
use App\Enums\BookingStatus;
use App\Exceptions\BookingNotPossible;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Night;
use App\Models\Slot;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request, Night $night, RequestSlot $requestSlot): RedirectResponse
    {
        abort_unless($night->is_published, 404);

        $preferred = $request->filled('slot_id')
            ? Slot::query()->findOrFail($request->integer('slot_id'))
            : null;

        try {
            $booking = $requestSlot->handle($night, $request->safe()->except('slot_id'), $preferred);
        } catch (BookingNotPossible $e) {
            return back()->withInput()->withErrors(['booking' => $e->getMessage()]);
        }

        $message = $booking->status === BookingStatus::Confirmed
            ? 'You are on the lineup! Check your email for the details and a cancel link.'
            : 'The night is full, so you are on the waitlist. We will email you if a slot opens up.';

        return to_route('nights.show', $night)->with('status', $message);
    }

    /** Confirmation page for the signed link in the DJ's email. */
    public function confirmCancel(Booking $booking): View
    {
        return view('bookings.cancel', ['booking' => $booking->load('night', 'slot')]);
    }

    public function cancel(Booking $booking, CancelBooking $cancelBooking): RedirectResponse
    {
        $cancelBooking->handle($booking);

        return to_route('nights.show', $booking->night)
            ->with('status', 'Your booking is cancelled. Thanks for freeing the spot!');
    }
}

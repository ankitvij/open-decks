<x-layouts.app title="Cancel booking">
    <div class="mx-auto max-w-md rounded-xl border border-zinc-800 bg-zinc-900 p-6">
        @if ($booking->status === \App\Enums\BookingStatus::Cancelled)
            <h1 class="text-xl font-semibold">This booking is already cancelled</h1>
            <p class="mt-2 text-zinc-400">Nothing else to do. Thanks for letting us know!</p>
        @else
            <h1 class="text-xl font-semibold">Cancel your booking?</h1>
            <p class="mt-2 text-zinc-300">
                {{ $booking->dj_name }} · {{ $booking->night->title }}<br>
                <span class="text-zinc-400">
                    @if ($booking->slot)
                        Slot {{ $booking->slot->starts_at->format('D, j M · H:i') }}
                    @else
                        {{ $booking->status->label() }}
                    @endif
                </span>
            </p>
            <p class="mt-4 text-sm text-zinc-400">Your spot goes to the next DJ on the waitlist straight away.</p>
            <form method="POST" action="{{ $booking->cancelActionUrl() }}" class="mt-6">
                @csrf
                <button class="w-full rounded-lg bg-rose-600 px-4 py-2 font-semibold hover:bg-rose-500">Yes, cancel my booking</button>
            </form>
        @endif
    </div>
</x-layouts.app>

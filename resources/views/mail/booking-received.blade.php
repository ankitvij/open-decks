<x-mail::message>
# Hi {{ $booking->dj_name }},

@if ($djSlot)
You're on the lineup for **{{ $night->title }}** at {{ $night->venue }}.

Your slot: **{{ $djSlot->starts_at->format('D, j M Y, H:i') }}–{{ $djSlot->endsAt()->format('H:i') }}** ({{ $night->slot_minutes }} minutes).
@else
**{{ $night->title }}** is fully booked right now, so you're on the waitlist. If a slot opens up, it's yours automatically and we'll email you straight away.
@endif

Can't make it? Please free your spot for someone else:

<x-mail::button :url="$cancelUrl">
Cancel my booking
</x-mail::button>

See you on the decks,<br>
{{ config('app.name') }}
</x-mail::message>

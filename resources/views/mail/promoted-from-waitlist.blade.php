<x-mail::message>
# Good news, {{ $booking->dj_name }}!

A slot at **{{ $night->title }}** ({{ $night->venue }}) just opened up, and it's yours.

Your slot: **{{ $djSlot->starts_at->format('D, j M Y, H:i') }}–{{ $djSlot->endsAt()->format('H:i') }}**.

If you can't make it after all, please let the next DJ have it:

<x-mail::button :url="$cancelUrl">
Cancel my booking
</x-mail::button>

See you on the decks,<br>
{{ config('app.name') }}
</x-mail::message>

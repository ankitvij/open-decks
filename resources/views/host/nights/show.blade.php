<x-layouts.app :title="$night->title">
    <a href="{{ route('host.nights.index') }}" class="text-sm text-zinc-400 hover:text-white">&larr; My nights</a>
    <div class="mt-3 flex flex-wrap items-baseline justify-between gap-2">
        <h1 class="text-2xl font-bold">{{ $night->title }}</h1>
        <a href="{{ route('nights.show', $night) }}" class="text-sm text-fuchsia-400 hover:text-fuchsia-300">Public page &rarr;</a>
    </div>
    <p class="text-zinc-400">{{ $night->starts_at->format('l, j F Y · H:i') }} · {{ $night->venue }}</p>

    <h2 class="mt-8 text-lg font-semibold">Lineup</h2>
    <table class="mt-3 w-full text-left text-sm">
        <thead class="text-zinc-400">
            <tr>
                <th class="py-2 font-medium">Time</th>
                <th class="py-2 font-medium">DJ</th>
                <th class="py-2 font-medium">Contact</th>
                <th class="py-2"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-800">
            @foreach ($night->slots as $timeSlot)
                <tr>
                    <td class="py-3 font-mono text-zinc-400">{{ $timeSlot->starts_at->format('H:i') }}</td>
                    @if ($booking = $timeSlot->booking)
                        <td class="py-3">
                            {{ $booking->dj_name }}
                            @if ($booking->mix_url)
                                <a href="{{ $booking->mix_url }}" class="ml-1 text-fuchsia-400 hover:underline" target="_blank" rel="noopener">mix</a>
                            @endif
                        </td>
                        <td class="py-3 text-zinc-400">{{ $booking->email }}</td>
                        <td class="py-3 text-right">
                            <form method="POST" action="{{ route('host.nights.bookings.destroy', [$night, $booking]) }}"
                                  onsubmit="return confirm('Remove this DJ and move up the waitlist?')">
                                @csrf @method('DELETE')
                                <button class="text-rose-400 hover:text-rose-300">Remove</button>
                            </form>
                        </td>
                    @else
                        <td class="py-3 text-emerald-400" colspan="3">Free</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="mt-8 text-lg font-semibold">Waitlist</h2>
    @if ($waitlist->isEmpty())
        <p class="mt-2 text-sm text-zinc-400">Nobody is waiting.</p>
    @else
        <ol class="mt-3 list-decimal space-y-1 pl-5 text-sm">
            @foreach ($waitlist as $waiting)
                <li>{{ $waiting->dj_name }} <span class="text-zinc-400">· {{ $waiting->email }} · since {{ $waiting->created_at->diffForHumans() }}</span></li>
            @endforeach
        </ol>
    @endif
</x-layouts.app>

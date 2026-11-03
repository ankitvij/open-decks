<x-layouts.app title="Upcoming nights">
    <h1 class="text-3xl font-bold tracking-tight">Open decks, open doors.</h1>
    <p class="mt-2 text-zinc-400">Grab a slot, bring your music, play to a real crowd. No account needed.</p>

    <div class="mt-8 space-y-4">
        @forelse ($nights as $night)
            <a href="{{ route('nights.show', $night) }}"
               class="block rounded-xl border border-zinc-800 bg-zinc-900 p-5 transition hover:border-fuchsia-600">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 class="text-lg font-semibold">{{ $night->title }}</h2>
                    @if ($night->free_slots_count > 0)
                        <span class="rounded-full bg-emerald-900 px-3 py-1 text-xs font-medium text-emerald-200">
                            {{ $night->free_slots_count }} {{ Str::plural('slot', $night->free_slots_count) }} free
                        </span>
                    @else
                        <span class="rounded-full bg-amber-900 px-3 py-1 text-xs font-medium text-amber-200">Full · join the waitlist</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-zinc-400">
                    {{ $night->starts_at->format('D, j M Y · H:i') }}–{{ $night->endsAt()->format('H:i') }} · {{ $night->venue }}
                </p>
            </a>
        @empty
            <p class="rounded-xl border border-dashed border-zinc-800 p-8 text-center text-zinc-400">
                No nights scheduled yet. Check back soon.
            </p>
        @endforelse
    </div>
</x-layouts.app>

<x-layouts.app :title="$night->title">
    <a href="{{ route('nights.index') }}" class="text-sm text-zinc-400 hover:text-white">&larr; All nights</a>

    <h1 class="mt-3 text-3xl font-bold tracking-tight">{{ $night->title }}</h1>
    <p class="mt-1 text-zinc-400">
        {{ $night->starts_at->format('l, j F Y · H:i') }}–{{ $night->endsAt()->format('H:i') }} · {{ $night->venue }}
    </p>
    @if ($night->description)
        <p class="mt-4 whitespace-pre-line text-zinc-300">{{ $night->description }}</p>
    @endif

    <div class="mt-8 grid gap-8 md:grid-cols-5">
        <section class="md:col-span-3" aria-labelledby="lineup">
            <h2 id="lineup" class="text-lg font-semibold">Lineup</h2>
            <ol class="mt-3 divide-y divide-zinc-800 rounded-xl border border-zinc-800">
                @foreach ($night->slots as $timeSlot)
                    <li class="flex items-center justify-between px-4 py-3">
                        <span class="font-mono text-sm text-zinc-400">
                            {{ $timeSlot->starts_at->format('H:i') }}–{{ $timeSlot->endsAt()->format('H:i') }}
                        </span>
                        @if ($timeSlot->booking)
                            <span class="font-medium">
                                {{ $timeSlot->booking->dj_name }}
                                @if ($timeSlot->booking->genre)
                                    <span class="text-sm font-normal text-zinc-400">· {{ $timeSlot->booking->genre }}</span>
                                @endif
                            </span>
                        @else
                            <span class="text-sm text-emerald-400">Free</span>
                        @endif
                    </li>
                @endforeach
            </ol>
            @if ($waitlistCount > 0)
                <p class="mt-3 text-sm text-zinc-400">{{ $waitlistCount }} {{ Str::plural('DJ', $waitlistCount) }} on the waitlist.</p>
            @endif
        </section>

        <section class="md:col-span-2" aria-labelledby="book">
            <h2 id="book" class="text-lg font-semibold">
                {{ $freeSlots->isEmpty() ? 'Join the waitlist' : 'Book a slot' }}
            </h2>

            @if (! $night->isOpenForBookings())
                <p class="mt-3 text-zinc-400">Bookings for this night are closed.</p>
            @else
                <form method="POST" action="{{ route('bookings.store', $night) }}" class="mt-3 space-y-4">
                    @csrf
                    <x-field name="dj_name" label="DJ name" required />
                    <x-field name="email" label="Email" type="email" required
                             hint="We'll send your slot and a cancel link here." />
                    <x-field name="genre" label="Genre (optional)" placeholder="e.g. Afro house, techno" />
                    <x-field name="mix_url" label="Link to a mix (optional)" type="url" placeholder="https://" />

                    @if ($freeSlots->isNotEmpty())
                        <div>
                            <label for="slot_id" class="block text-sm font-medium">Preferred slot</label>
                            <select id="slot_id" name="slot_id"
                                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2">
                                <option value="">Earliest free slot</option>
                                @foreach ($freeSlots as $timeSlot)
                                    <option value="{{ $timeSlot->id }}" @selected(old('slot_id') == $timeSlot->id)>
                                        {{ $timeSlot->starts_at->format('H:i') }}–{{ $timeSlot->endsAt()->format('H:i') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('slot_id') <p class="mt-1 text-sm text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <button class="w-full rounded-lg bg-fuchsia-600 px-4 py-2 font-semibold hover:bg-fuchsia-500">
                        {{ $freeSlots->isEmpty() ? 'Join the waitlist' : 'Book my slot' }}
                    </button>
                </form>
            @endif
        </section>
    </div>
</x-layouts.app>

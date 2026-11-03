<x-layouts.app title="My nights">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">My nights</h1>
        <a href="{{ route('host.nights.create') }}" class="rounded-lg bg-fuchsia-600 px-4 py-2 text-sm font-semibold hover:bg-fuchsia-500">New night</a>
    </div>

    <table class="mt-6 w-full text-left text-sm">
        <thead class="text-zinc-400">
            <tr>
                <th class="py-2 font-medium">Night</th>
                <th class="py-2 font-medium">Date</th>
                <th class="py-2 font-medium">Booked</th>
                <th class="py-2 font-medium">Waitlist</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-800">
            @forelse ($nights as $night)
                <tr>
                    <td class="py-3"><a class="font-medium hover:text-fuchsia-400" href="{{ route('host.nights.show', $night) }}">{{ $night->title }}</a></td>
                    <td class="py-3 text-zinc-400">{{ $night->starts_at->format('j M Y, H:i') }}</td>
                    <td class="py-3">{{ $night->booked_slots_count }}/{{ $night->slots_count }}</td>
                    <td class="py-3">{{ $night->waitlist_count }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-8 text-center text-zinc-400">No nights yet. Create your first one.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-layouts.app>

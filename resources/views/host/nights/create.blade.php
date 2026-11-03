<x-layouts.app title="New night">
    <div class="mx-auto max-w-lg">
        <h1 class="text-2xl font-bold">New night</h1>
        <form method="POST" action="{{ route('host.nights.store') }}" class="mt-6 space-y-4">
            @csrf
            <x-field name="title" label="Title" required placeholder="Wednesday Open Decks" />
            <x-field name="venue" label="Venue" required />
            <x-field name="starts_at" label="Starts at" type="datetime-local" required />
            <div class="grid grid-cols-2 gap-4">
                <x-field name="slot_minutes" label="Slot length (minutes)" type="number" min="15" max="180" :value="45" required />
                <x-field name="slot_count" label="Number of slots" type="number" min="1" max="24" :value="6" required />
            </div>
            <div>
                <label for="description" class="block text-sm font-medium">Description (optional)</label>
                <textarea id="description" name="description" rows="4"
                          class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2">{{ old('description') }}</textarea>
            </div>
            <button class="w-full rounded-lg bg-fuchsia-600 px-4 py-2 font-semibold hover:bg-fuchsia-500">Create night and slots</button>
        </form>
    </div>
</x-layouts.app>

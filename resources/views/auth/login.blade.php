<x-layouts.app title="Host login">
    <div class="mx-auto max-w-sm">
        <h1 class="text-2xl font-bold">Host login</h1>
        <p class="mt-1 text-sm text-zinc-400">Run your nights and manage the lineup.</p>
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <x-field name="email" label="Email" type="email" required autofocus />
            <div>
                <label for="password" class="block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2">
            </div>
            <label class="flex items-center gap-2 text-sm text-zinc-400">
                <input type="checkbox" name="remember" class="rounded border-zinc-700 bg-zinc-900"> Remember me
            </label>
            <button class="w-full rounded-lg bg-fuchsia-600 px-4 py-2 font-semibold hover:bg-fuchsia-500">Log in</button>
        </form>
    </div>
</x-layouts.app>

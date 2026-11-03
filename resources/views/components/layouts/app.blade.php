<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full text-zinc-100 antialiased">
    <header class="border-b border-zinc-800">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4">
            <a href="{{ route('nights.index') }}" class="flex items-center gap-2 font-semibold tracking-tight">
                <span class="inline-block h-3 w-3 rounded-full bg-fuchsia-500 shadow-[0_0_12px] shadow-fuchsia-500"></span>
                {{ config('app.name') }}
            </a>
            <nav class="flex items-center gap-4 text-sm text-zinc-400">
                @auth
                    <a href="{{ route('host.nights.index') }}" class="hover:text-white">My nights</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="hover:text-white">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-white">Host login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-8">
        @if (session('status'))
            <div role="status" class="mb-6 rounded-lg border border-emerald-700 bg-emerald-950 px-4 py-3 text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('booking'))
            <div role="alert" class="mb-6 rounded-lg border border-rose-700 bg-rose-950 px-4 py-3 text-rose-200">
                {{ $errors->first('booking') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>

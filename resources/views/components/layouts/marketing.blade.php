@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name').' — HR & Payroll Software' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white antialiased">
    <header class="border-b border-slate-200">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-x-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600">
                    <i class="bx bxs-buildings text-lg text-white"></i>
                </span>
                <span class="text-lg font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
            </a>

            <nav class="hidden items-center gap-x-8 text-sm font-medium text-slate-600 md:flex">
                <a href="#features" class="hover:text-slate-900">Features</a>
                <a href="#how-it-works" class="hover:text-slate-900">How it works</a>
            </nav>

            <a href="{{ route('login') }}">
                <x-button variant="secondary">Log in</x-button>
            </a>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-y-4 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:px-6">
            <span class="flex items-center gap-x-2 font-semibold text-slate-700">
                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-600">
                    <i class="bx bxs-buildings text-xs text-white"></i>
                </span>
                {{ config('app.name') }}
            </span>
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. HR &amp; Payroll software for growing organizations.</p>
        </div>
    </footer>
</body>
</html>

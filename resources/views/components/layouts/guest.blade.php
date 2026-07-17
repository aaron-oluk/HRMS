@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <div class="mb-8 text-center">
            <span class="text-2xl font-bold tracking-tight text-indigo-600">{{ config('app.name') }}</span>
            <p class="mt-1 text-sm text-slate-500">HR &amp; Payroll for Uganda</p>
        </div>

        <div class="w-full max-w-md">
            <x-card>
                {{ $slot }}
            </x-card>
        </div>
    </div>
</body>
</html>

@props(['title' => null, 'header' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }} · Platform Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 antialiased">
    <header class="border-b border-slate-200 bg-white px-4 sm:px-6">
        <div class="mx-auto flex h-16 max-w-5xl items-center justify-between">
            <a href="{{ route('admin.tenants.index') }}" class="flex items-center gap-x-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900">
                    <i class="bx bxs-buildings text-lg text-white"></i>
                </span>
                <span class="text-lg font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-slate-500">Platform Admin</span>
            </a>
            <nav class="flex items-center gap-x-1">
                <a href="{{ route('admin.tenants.index') }}" class="rounded-md px-3 py-1.5 text-sm font-medium {{ request()->routeIs('admin.tenants.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:text-slate-900' }}">
                    Companies
                </a>
                <a href="{{ route('admin.super-admins.index') }}" class="rounded-md px-3 py-1.5 text-sm font-medium {{ request()->routeIs('admin.super-admins.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:text-slate-900' }}">
                    Platform Admins
                </a>
                <a href="{{ route('admin.statutory.edit') }}" class="rounded-md px-3 py-1.5 text-sm font-medium {{ request()->routeIs('admin.statutory.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:text-slate-900' }}">
                    Statutory Config
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-x-1 rounded-md px-3 py-1.5 text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
                        <i class="bx bx-log-out text-base"></i>
                        Log out
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <h1 class="mb-6 text-lg font-semibold text-slate-900">{{ $header ?? '' }}</h1>

        @if (session('status'))
            <x-alert type="success" class="mb-6">{{ session('status') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
        @endif

        {{ $slot }}
    </main>
</body>
</html>

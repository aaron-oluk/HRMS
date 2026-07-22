@props(['title' => null, 'header' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }} · Platform Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 antialiased" x-data="{ sidebarOpen: false }">
<div class="min-h-full">
    <div class="lg:hidden fixed inset-x-0 top-0 z-40 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3">
        <span class="flex items-center gap-x-2 text-lg font-bold text-slate-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900">
                <i class="bx bxs-buildings text-lg text-white"></i>
            </span>
            {{ config('app.name') }}
        </span>
        <button @click="sidebarOpen = !sidebarOpen" class="rounded-md p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
            <i class="bx bx-menu text-2xl"></i>
        </button>
    </div>

    <div
        x-cloak
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col transform border-r border-slate-200 bg-white transition-transform duration-200 ease-in-out lg:translate-x-0"
    >
        <div class="flex h-16 shrink-0 items-center gap-x-2.5 px-6">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900">
                <i class="bx bxs-buildings text-lg text-white"></i>
            </span>
            <div class="min-w-0">
                <span class="block truncate text-lg font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
                <span class="block text-xs font-medium uppercase tracking-wide text-slate-400">Platform Admin</span>
            </div>
        </div>

        <nav class="flex flex-1 flex-col gap-y-1 overflow-y-auto px-3 pb-4">
            <x-nav-link :href="route('admin.tenants.index')" :active="request()->routeIs('admin.tenants.*')" icon="bx-buildings">
                Companies
            </x-nav-link>
            @if (auth()->user()->is_super_admin)
                <x-nav-link :href="route('admin.super-admins.index')" :active="request()->routeIs('admin.super-admins.*')" icon="bx-user-circle">
                    Platform Admins
                </x-nav-link>
                <x-nav-link :href="route('admin.themes.index')" :active="request()->routeIs('admin.themes.*')" icon="bx-palette">
                    Themes
                </x-nav-link>
            @endif
        </nav>

        <div class="border-t border-slate-100 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
                    <i class="bx bx-log-out text-lg text-slate-400"></i>
                    Log out
                </button>
            </form>
        </div>
    </div>

    <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden"></div>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-10 flex h-16 items-center border-b border-slate-200 bg-white/80 px-4 pt-12 backdrop-blur lg:pt-0 sm:px-6">
            <h1 class="text-lg font-semibold text-slate-900">{{ $header ?? '' }}</h1>
        </header>

        <main class="px-4 py-8 sm:px-6">
            @if (session('status'))
                <x-alert type="success" class="mb-6">{{ session('status') }}</x-alert>
            @endif

            @if (session('error'))
                <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>

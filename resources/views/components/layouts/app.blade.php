@props(['title' => null, 'header' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 antialiased" x-data="{ sidebarOpen: false }">
<div class="min-h-full">
    <div class="lg:hidden fixed inset-x-0 top-0 z-40 flex items-center justify-between bg-indigo-800 px-4 py-3">
        <span class="text-lg font-bold text-white">{{ config('app.name') }}</span>
        <button @click="sidebarOpen = !sidebarOpen" class="text-indigo-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <div
        x-cloak
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-30 w-64 transform bg-indigo-800 transition-transform duration-200 ease-in-out lg:translate-x-0"
    >
        <div class="flex h-16 items-center px-6">
            <span class="text-lg font-bold text-white">{{ config('app.name') }}</span>
        </div>
        <nav class="mt-2 flex flex-1 flex-col gap-y-1 px-3">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-nav-link>

            @can('employees.view')
                <x-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                    Employees
                </x-nav-link>
            @endcan

            @can('org.view')
                <x-nav-link :href="route('entities.index')" :active="request()->routeIs('entities.*')">
                    Entities
                </x-nav-link>
                <x-nav-link :href="route('branches.index')" :active="request()->routeIs('branches.*')">
                    Branches
                </x-nav-link>
                <x-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                    Departments
                </x-nav-link>
                <x-nav-link :href="route('positions.index')" :active="request()->routeIs('positions.*')">
                    Positions
                </x-nav-link>
                <x-nav-link :href="route('grades.index')" :active="request()->routeIs('grades.*')">
                    Grades
                </x-nav-link>
            @endcan

            @can('users.manage')
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    Users &amp; Roles
                </x-nav-link>
            @endcan

            <x-nav-link :href="route('security.edit')" :active="request()->routeIs('security.*')">
                Security
            </x-nav-link>
        </nav>
    </div>

    <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

    <div class="lg:pl-64">
        <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 pt-12 lg:pt-0 sm:px-6">
            <h1 class="text-lg font-semibold text-slate-900">{{ $header ?? '' }}</h1>
            <div class="flex items-center gap-x-4">
                <span class="text-sm text-slate-500">{{ auth()->user()->tenant?->name }}</span>
                <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-500 hover:text-slate-700">Log out</button>
                </form>
            </div>
        </header>

        <main class="px-4 py-8 sm:px-6">
            @if (session('status'))
                <x-alert type="success" class="mb-6">{{ session('status') }}</x-alert>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>

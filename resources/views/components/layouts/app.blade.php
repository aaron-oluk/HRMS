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
        <span class="flex items-center gap-x-2 text-lg font-bold text-white">
            <i class="bx bxs-buildings text-xl text-indigo-300"></i>
            {{ config('app.name') }}
        </span>
        <button @click="sidebarOpen = !sidebarOpen" class="text-indigo-100">
            <i class="bx bx-menu text-2xl"></i>
        </button>
    </div>

    <div
        x-cloak
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col transform bg-indigo-800 transition-transform duration-200 ease-in-out lg:translate-x-0"
    >
        <div class="flex h-16 shrink-0 items-center gap-x-2 px-6">
            <i class="bx bxs-buildings text-xl text-indigo-300"></i>
            <span class="text-lg font-bold text-white">{{ config('app.name') }}</span>
        </div>
        <nav class="flex flex-1 flex-col gap-y-1 overflow-y-auto px-3 pb-4">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="bx-grid-alt">
                Dashboard
            </x-nav-link>

            @php($totalInboxCount = $pendingLeaveApprovalsCount + $pendingOvertimeApprovalsCount)
            <x-nav-link :href="route('inbox.index')" :active="request()->routeIs('inbox.index')" icon="bxs-inbox">
                Inbox
                @if ($totalInboxCount > 0)
                    <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-indigo-800">{{ $totalInboxCount }}</span>
                @endif
            </x-nav-link>

            @can('employees.view')
                <x-nav-section>People</x-nav-section>
                <x-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')" icon="bx-group">
                    Employees
                </x-nav-link>
            @else
                <x-nav-section>People</x-nav-section>
            @endcan

            <x-nav-link :href="route('leave.index')" :active="request()->routeIs('leave.index')" icon="bx-calendar-check">
                Time Off
                @if ($pendingLeaveApprovalsCount > 0)
                    <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-indigo-800">{{ $pendingLeaveApprovalsCount }}</span>
                @endif
            </x-nav-link>

            <x-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')" icon="bx-time-five">
                Attendance
                @if ($pendingOvertimeApprovalsCount > 0)
                    <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-indigo-800">{{ $pendingOvertimeApprovalsCount }}</span>
                @endif
            </x-nav-link>

            @can('org.view')
                <x-nav-section>Organization</x-nav-section>
                <x-nav-link :href="route('entities.index')" :active="request()->routeIs('entities.*')" icon="bx-buildings">
                    Entities
                </x-nav-link>
                <x-nav-link :href="route('branches.index')" :active="request()->routeIs('branches.*')" icon="bx-git-branch">
                    Branches
                </x-nav-link>
                <x-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')" icon="bx-sitemap">
                    Departments
                </x-nav-link>
                <x-nav-link :href="route('positions.index')" :active="request()->routeIs('positions.*')" icon="bx-briefcase">
                    Positions
                </x-nav-link>
                <x-nav-link :href="route('grades.index')" :active="request()->routeIs('grades.*')" icon="bx-layer">
                    Grades
                </x-nav-link>
                <x-nav-link :href="route('leave-types.index')" :active="request()->routeIs('leave-types.*')" icon="bx-calendar-star">
                    Leave Types
                </x-nav-link>
                <x-nav-link :href="route('shifts.index')" :active="request()->routeIs('shifts.*')" icon="bx-alarm">
                    Shifts
                </x-nav-link>
            @endcan

            @can('users.manage')
                <x-nav-section>Administration</x-nav-section>
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon="bx-user-circle">
                    Users &amp; Roles
                </x-nav-link>
            @endcan

            @cannot('users.manage')
                <x-nav-section>Account</x-nav-section>
            @endcannot
            <x-nav-link :href="route('security.edit')" :active="request()->routeIs('security.*')" icon="bx-shield-quarter">
                Security
            </x-nav-link>
        </nav>
    </div>

    <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

    <div class="lg:pl-64">
        <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 pt-12 lg:pt-0 sm:px-6">
            <h1 class="flex items-center gap-x-2 text-lg font-semibold text-slate-900">
                {{ $header ?? '' }}
            </h1>
            <div class="flex items-center gap-x-4">
                <span class="hidden items-center gap-x-1.5 text-sm text-slate-500 sm:flex">
                    <i class="bx bxs-buildings text-base"></i>
                    {{ auth()->user()->tenant?->name }}
                </span>
                <span class="flex items-center gap-x-1.5 text-sm font-medium text-slate-700">
                    <i class="bx bxs-user-circle text-lg text-slate-400"></i>
                    {{ auth()->user()->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-x-1 text-sm text-slate-500 hover:text-slate-700">
                        <i class="bx bx-log-out text-base"></i>
                        Log out
                    </button>
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

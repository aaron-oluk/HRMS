@props(['title' => null, 'header' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 antialiased" x-data="{ sidebarOpen: false }">
<div class="min-h-full">
    <div class="lg:hidden fixed inset-x-0 top-0 z-40 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3">
        <span class="flex items-center gap-x-2 text-lg font-bold text-slate-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600">
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
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600">
                <i class="bx bxs-buildings text-lg text-white"></i>
            </span>
            <span class="text-lg font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
        </div>
        <nav class="flex flex-1 flex-col gap-y-1 overflow-y-auto px-3 pb-4">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="bx-grid-alt">
                Dashboard
            </x-nav-link>

            @php($totalInboxCount = $pendingLeaveApprovalsCount + $pendingOvertimeApprovalsCount)
            <x-nav-link :href="route('inbox.index')" :active="request()->routeIs('inbox.index')" icon="bxs-inbox">
                Inbox
                @if ($totalInboxCount > 0)
                    <span class="ml-auto rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $totalInboxCount }}</span>
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
                    <span class="ml-auto rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $pendingLeaveApprovalsCount }}</span>
                @endif
            </x-nav-link>

            <x-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')" icon="bx-time-five">
                Attendance
                @if ($pendingOvertimeApprovalsCount > 0)
                    <span class="ml-auto rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $pendingOvertimeApprovalsCount }}</span>
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

            @canany(['users.manage', 'audit.view'])
                <x-nav-section>Administration</x-nav-section>
                @can('users.manage')
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon="bx-user-circle">
                        Users &amp; Roles
                    </x-nav-link>
                @endcan
                @can('audit.view')
                    <x-nav-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')" icon="bx-history">
                        Audit Log
                    </x-nav-link>
                @endcan
            @endcanany

            @cannot('users.manage')
                @cannot('audit.view')
                    <x-nav-section>Account</x-nav-section>
                @endcannot
            @endcannot
            <x-nav-link :href="route('security.edit')" :active="request()->routeIs('security.*')" icon="bx-shield-quarter">
                Security
            </x-nav-link>
        </nav>

        <div class="flex items-center gap-x-2 border-t border-slate-100 px-4 py-3">
            <i class="bx bxs-buildings text-sm text-emerald-500"></i>
            <span class="truncate text-xs font-medium text-slate-500">{{ auth()->user()->tenant?->name }}</span>
        </div>
    </div>

    <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden"></div>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 pt-12 backdrop-blur lg:pt-0 sm:px-6">
            <h1 class="flex items-center gap-x-2 text-lg font-semibold text-slate-900">
                {{ $header ?? '' }}
            </h1>
            <div class="flex items-center gap-x-4">
                <div class="flex items-center gap-x-2.5">
                    <x-avatar :name="auth()->user()->name" size="sm" />
                    <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ auth()->user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-x-1 rounded-md px-2 py-1 text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                        <i class="bx bx-log-out text-base"></i>
                        <span class="hidden sm:inline">Log out</span>
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

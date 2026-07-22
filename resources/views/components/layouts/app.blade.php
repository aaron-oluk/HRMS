@props(['title' => null, 'header' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (! $activeTheme->is_default)
        <style>
            :root {
                --theme-50: {{ $activeTheme->color_50 }};
                --theme-100: {{ $activeTheme->color_100 }};
                --theme-500: {{ $activeTheme->color_500 }};
                --theme-600: {{ $activeTheme->color_600 }};
                --theme-700: {{ $activeTheme->color_700 }};
                --theme-800: {{ $activeTheme->color_800 }};
                --theme-font: {{ $activeTheme->font_stack }};
            }
        </style>
    @endif
</head>
<body class="h-full bg-slate-50 antialiased" x-data="{ sidebarOpen: false }">
@if (session('impersonator_id'))
    <div class="flex items-center justify-center gap-x-3 bg-amber-500 px-4 py-2 text-center text-sm font-medium text-amber-950">
        <i class="bx bxs-user-detail text-base"></i>
        <span>Viewing as {{ auth()->user()->name }} ({{ auth()->user()->tenant?->name }}) &mdash; changes you make are real.</span>
        <form method="POST" action="{{ route('impersonation.stop') }}">
            @csrf
            <button type="submit" class="font-semibold underline hover:no-underline">Return to admin console</button>
        </form>
    </div>
@endif
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

            @if (auth()->user()->employee)
                <x-nav-link :href="route('warnings.mine')" :active="request()->routeIs('warnings.mine')" icon="bx-shield-quarter">
                    My Warnings
                </x-nav-link>
            @endif

            @if ($enabledModules['payroll'] && (auth()->user()->canAny(['payroll.view', 'payroll.view-team-summary', 'payroll.run']) || auth()->user()->employee))
                <x-nav-dropdown label="Payroll" icon="bx-receipt" :active="request()->routeIs('payroll.*')">
                    @canany(['payroll.view', 'payroll.view-team-summary', 'payroll.run'])
                        <x-nav-link :href="route('payroll.runs.index')" :active="request()->routeIs('payroll.runs.*')" icon="bx-list-ul">
                            Payroll Runs
                        </x-nav-link>
                    @endcanany
                    @if (auth()->user()->employee)
                        <x-nav-link :href="route('payroll.my-payslips')" :active="request()->routeIs('payroll.my-payslips')" icon="bx-receipt">
                            My Payslips
                        </x-nav-link>
                    @endif
                </x-nav-dropdown>
            @endif

            @if ($enabledModules['recruitment'])
                @canany(['recruitment.view', 'recruitment.manage'])
                    <x-nav-link :href="route('recruitment.requisitions.index')" :active="request()->routeIs('recruitment.requisitions.*')" icon="bx-briefcase-alt-2">
                        Recruitment
                    </x-nav-link>
                @endcanany
            @endif

            @if ($enabledModules['performance'] && (auth()->user()->can('performance.view') || auth()->user()->employee))
                <x-nav-dropdown label="Performance" icon="bx-line-chart" :active="request()->routeIs('performance.*')">
                    @can('performance.view')
                        <x-nav-link :href="route('performance.cycles.index')" :active="request()->routeIs('performance.cycles.*')" icon="bx-refresh">
                            Review Cycles
                        </x-nav-link>
                    @endcan
                    @if (auth()->user()->employee)
                        <x-nav-link :href="route('performance.my')" :active="request()->routeIs('performance.my')" icon="bx-user-check">
                            My Performance
                        </x-nav-link>
                    @endif
                </x-nav-dropdown>
            @endif

            @if ($enabledModules['engagement'])
                @can('engagement.manage')
                    <x-nav-link :href="route('engagement.surveys.index')" :active="request()->routeIs('engagement.surveys.*')" icon="bx-message-square-detail">
                        Engagement
                    </x-nav-link>
                @endcan
            @endif

            @if ($enabledModules['cases'])
                <x-nav-link :href="route('cases.index')" :active="request()->routeIs('cases.*')" icon="bx-support">
                    {{ auth()->user()->can('cases.manage') ? 'Cases' : 'My Cases' }}
                </x-nav-link>
            @endif

            @if ($enabledModules['reports'])
                @can('reports.view')
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="bx-bar-chart-alt-2">
                        Reports
                    </x-nav-link>
                @endcan
            @endif

            <x-nav-dropdown label="Documents" icon="bx-file-blank" :active="request()->routeIs('documents.*')">
                @if ($enabledModules['esignature'])
                    @can('esignature.send')
                        <x-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.index') || request()->routeIs('documents.show') || request()->routeIs('documents.create')" icon="bx-send">
                            Sent Documents
                        </x-nav-link>
                    @endcan
                @endif
                <x-nav-link :href="route('documents.signature.edit')" :active="request()->routeIs('documents.signature.edit')" icon="bx-pen">
                    My Signature
                </x-nav-link>
            </x-nav-dropdown>

            @can('org.view')
                <x-nav-dropdown label="Organization" icon="bx-buildings" :active="request()->routeIs(['entities.*', 'areas.*', 'branches.*', 'departments.*', 'positions.*', 'grades.*', 'leave-types.*', 'shifts.*', 'organization.*'])">
                    <x-nav-link :href="route('entities.index')" :active="request()->routeIs('entities.*')" icon="bx-buildings">
                        Entities
                    </x-nav-link>
                    @if (auth()->user()->tenant?->isSegmented())
                        <x-nav-link :href="route('areas.index')" :active="request()->routeIs('areas.*')" icon="bx-map-alt">
                            Areas
                        </x-nav-link>
                    @endif
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
                    @can('org.manage')
                        <x-nav-link :href="route('organization.edit')" :active="request()->routeIs('organization.*')" icon="bx-cog">
                            Settings
                        </x-nav-link>
                    @endcan
                </x-nav-dropdown>
            @endcan

            @canany(['users.manage', 'audit.view'])
                <x-nav-dropdown label="Administration" icon="bx-cog" :active="request()->routeIs(['users.*', 'audit-logs.*'])">
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
                </x-nav-dropdown>
            @endcanany

            <x-nav-section>Account</x-nav-section>
            <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" icon="bx-user">
                My Profile
            </x-nav-link>
        </nav>

        <div class="border-t border-slate-100 px-4 py-3">
            <span class="truncate text-xs font-medium text-slate-500">{{ auth()->user()->tenant?->name }}</span>
        </div>
    </div>

    <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden"></div>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 pt-12 backdrop-blur lg:pt-0 sm:px-6">
            <h1 class="flex items-center gap-x-2 text-lg font-semibold text-slate-900">
                {{ $header ?? '' }}
            </h1>
            <div class="flex items-center gap-x-2">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false" class="relative flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                        <i class="bx bx-bell text-xl"></i>
                        @if ($unreadNotificationsCount > 0)
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-red-500"></span>
                        @endif
                    </button>

                    <div
                        x-cloak
                        x-show="open"
                        x-transition
                        class="absolute right-0 z-20 mt-2 w-80 origin-top-right rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <div class="flex items-center justify-between border-b border-slate-100 px-3 py-2.5">
                            <p class="text-sm font-semibold text-slate-900">Notifications</p>
                            @if ($unreadNotificationsCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">Mark all as read</button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            @forelse ($recentNotifications as $notification)
                                <a
                                    href="{{ route('notifications.read', $notification->id) }}"
                                    class="flex items-start gap-x-3 px-3 py-3 text-sm hover:bg-slate-50 {{ $notification->read_at ? '' : 'bg-emerald-50/60' }}"
                                >
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                                        <i class="bx {{ $notification->data['icon'] ?? 'bx-bell' }} text-base text-emerald-600"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-slate-900">{{ $notification->data['title'] }}</p>
                                        <p class="mt-0.5 text-slate-500">{{ $notification->data['message'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                    @unless ($notification->read_at)
                                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                                    @endunless
                                </a>
                            @empty
                                <p class="px-3 py-6 text-center text-sm text-slate-500">No notifications yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-x-2 rounded-lg px-1.5 py-1 transition hover:bg-slate-100">
                        <x-avatar :name="auth()->user()->name" size="sm" />
                        <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ auth()->user()->name }}</span>
                        <i class="bx bx-chevron-down hidden text-sm text-slate-400 sm:block"></i>
                    </button>

                    <div
                        x-cloak
                        x-show="open"
                        x-transition
                        class="absolute right-0 z-20 mt-2 w-56 origin-top-right rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <div class="flex items-center gap-x-3 border-b border-slate-100 px-3 py-3">
                            <x-avatar :name="auth()->user()->name" size="sm" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-x-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <i class="bx bx-user text-base text-slate-400"></i>
                            My Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-x-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">
                                <i class="bx bx-log-out text-base text-slate-400"></i>
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
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

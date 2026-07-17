@php
    $quickActions = collect([
        ['label' => 'Request time off', 'route' => 'leave.index', 'icon' => 'bx-calendar-plus', 'show' => $myLeaveBalance !== null],
        ['label' => 'Review approvals', 'route' => 'leave.index', 'icon' => 'bx-check-shield', 'show' => $pendingApprovalsCount !== null],
        ['label' => 'Add employee', 'route' => 'employees.create', 'icon' => 'bx-user-plus', 'show' => auth()->user()->can('employees.manage')],
        ['label' => 'Add user', 'route' => 'users.create', 'icon' => 'bx-user-circle', 'show' => auth()->user()->can('users.manage')],
    ])->filter(fn ($action) => $action['show']);

    $donutColors = ['#4338ca', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff'];
    $donutTotal = $departmentHeadcount->sum('total');
    $cumulative = 0;
    $donutSegments = $departmentHeadcount->values()->map(function ($row, $i) use (&$cumulative, $donutTotal, $donutColors) {
        $from = $donutTotal ? round($cumulative / $donutTotal * 100, 2) : 0;
        $cumulative += $row->total;
        $to = $donutTotal ? round($cumulative / $donutTotal * 100, 2) : 0;

        return ['color' => $donutColors[$i % count($donutColors)], 'from' => $from, 'to' => $to];
    });
@endphp

<x-layouts.app title="Dashboard" header="Dashboard">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @if ($myLeaveBalance !== null)
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                    <i class="bx bx-calendar-check text-2xl text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">My leave balance</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $myLeaveBalance }} <span class="text-sm font-normal text-slate-500">days</span></p>
                </div>
            </x-card>
        @endif

        @if ($pendingApprovalsCount !== null)
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                    <i class="bx bx-check-shield text-2xl text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Pending approvals</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $pendingApprovalsCount }}</p>
                </div>
            </x-card>
        @endif

        @can('employees.view')
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                    <i class="bx bx-group text-2xl text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Employees</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $employeeCount }}</p>
                </div>
            </x-card>

            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                    <i class="bx bxs-plane-alt text-2xl text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">On leave today</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $onLeaveTodayCount }}</p>
                </div>
            </x-card>
        @endcan

        @can('org.view')
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                    <i class="bx bx-buildings text-2xl text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Entities</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $entityCount }}</p>
                </div>
            </x-card>
        @endcan
    </div>

    @if ($quickActions->isNotEmpty())
        <x-card class="mt-6">
            <p class="mb-3 text-sm font-semibold text-slate-900">Quick actions</p>
            <div class="flex flex-wrap gap-3">
                @foreach ($quickActions as $action)
                    <a href="{{ route($action['route']) }}">
                        <x-button variant="secondary" :icon="$action['icon']">{{ $action['label'] }}</x-button>
                    </a>
                @endforeach
            </div>
        </x-card>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        @if ($departmentHeadcount->isNotEmpty())
            <x-card>
                <p class="mb-4 text-sm font-semibold text-slate-900">Headcount by department</p>
                <div class="flex items-center gap-x-8">
                    <div
                        class="h-32 w-32 shrink-0 rounded-full"
                        style="background: radial-gradient(circle, white 55%, transparent 56%), conic-gradient({{ $donutSegments->map(fn ($s) => "{$s['color']} {$s['from']}% {$s['to']}%")->implode(', ') }})"
                    ></div>
                    <div class="flex flex-col gap-y-2">
                        @foreach ($departmentHeadcount as $i => $row)
                            <div class="flex items-center gap-x-2 text-sm">
                                <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $donutColors[$i % count($donutColors)] }}"></span>
                                <span class="text-slate-700">{{ $row->department }}</span>
                                <span class="text-slate-400">· {{ $row->total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-card>
        @endif

        @if ($activity->isNotEmpty())
            <x-card>
                <p class="mb-4 text-sm font-semibold text-slate-900">Recent activity</p>
                <div class="flex flex-col">
                    @foreach ($activity as $item)
                        <div class="flex items-start gap-x-3 border-b border-slate-100 py-3 last:border-b-0 last:pb-0">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100">
                                <i class="bx {{ $item['icon'] }} text-base text-indigo-600"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-slate-700">{{ $item['text'] }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $item['time']->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        @if ($upcomingLeave->isNotEmpty())
            <x-card class="lg:col-span-2">
                <p class="mb-4 text-sm font-semibold text-slate-900">Upcoming time off</p>
                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach ($upcomingLeave as $request)
                        <div class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-x-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-50">
                                    <i class="bx bx-calendar text-base text-indigo-600"></i>
                                </div>
                                <p class="text-sm text-slate-700">{{ $request->employee->fullName() }} · {{ $request->leaveType->name }}</p>
                            </div>
                            <p class="text-sm text-slate-500">
                                {{ $request->start_date->toFormattedDateString() }}
                                @if (! $request->start_date->equalTo($request->end_date))
                                    &ndash; {{ $request->end_date->toFormattedDateString() }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>
</x-layouts.app>

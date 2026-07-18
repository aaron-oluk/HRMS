@php
    $greeting = match (true) {
        now()->hour < 12 => 'Good morning',
        now()->hour < 17 => 'Good afternoon',
        default => 'Good evening',
    };
    $firstName = str(auth()->user()->name)->before(' ')->toString();

    $quickActions = collect([
        ['label' => 'Request time off', 'route' => 'leave.index', 'icon' => 'bx-calendar-plus', 'show' => $myLeaveBalance !== null],
        ['label' => 'Review approvals', 'route' => 'leave.index', 'icon' => 'bx-check-shield', 'show' => $pendingApprovalsCount !== null],
        ['label' => 'Add employee', 'route' => 'employees.create', 'icon' => 'bx-user-plus', 'show' => auth()->user()->can('employees.create')],
        ['label' => 'Add user', 'route' => 'users.create', 'icon' => 'bx-user-circle', 'show' => auth()->user()->can('users.manage')],
    ])->filter(fn ($action) => $action['show']);

    // A validated categorical palette (fixed hue order, not shades of one color) —
    // see the dataviz skill's reference palette. Adjacent pairs clear the CVD and
    // normal-vision separation floors, so departments stay visually distinct.
    $donutColors = ['#2a78d6', '#008300', '#e87ba4', '#eda100', '#1baf7a', '#eb6834', '#4a3aa7', '#e34948'];
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
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">{{ $greeting }}, {{ $firstName }}</h2>
            <p class="mt-0.5 text-sm text-slate-500">{{ now()->format('l, F j, Y') }}</p>
        </div>
        @if ($quickActions->isNotEmpty())
            <div class="flex flex-wrap gap-2.5">
                @foreach ($quickActions as $action)
                    <a href="{{ route($action['route']) }}">
                        <x-button variant="secondary" :icon="$action['icon']">{{ $action['label'] }}</x-button>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @if ($myLeaveBalance !== null)
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <i class="bx bx-calendar-check text-2xl text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">My leave balance</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $myLeaveBalance }} <span class="text-sm font-normal text-slate-500">days</span></p>
                </div>
            </x-card>
        @endif

        @if ($pendingApprovalsCount !== null)
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <i class="bx bx-check-shield text-2xl text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Pending approvals</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $pendingApprovalsCount }}</p>
                </div>
            </x-card>
        @endif

        @can('employees.view')
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <i class="bx bx-group text-2xl text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Employees</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $employeeCount }}</p>
                </div>
            </x-card>

            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <i class="bx bxs-plane-alt text-2xl text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">On leave today</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $onLeaveTodayCount }}</p>
                </div>
            </x-card>
        @endcan

        @can('org.view')
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <i class="bx bx-buildings text-2xl text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Entities</p>
                    <p class="mt-1 text-3xl font-semibold text-slate-900">{{ $entityCount }}</p>
                </div>
            </x-card>
        @endcan
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            @if ($myWeeklyHours->isNotEmpty())
                <x-card>
                    <p class="mb-4 text-sm font-semibold text-slate-900">Hours this week</p>
                    @php
                        $maxHours = max(8, $myWeeklyHours->max('hours'));
                    @endphp
                    <div class="flex h-32 items-end justify-between gap-x-2">
                        @foreach ($myWeeklyHours as $day)
                            <div class="flex h-full flex-1 flex-col items-center justify-end gap-y-1.5">
                                <span class="text-xs text-slate-400">{{ $day['hours'] > 0 ? $day['hours'] : '' }}</span>
                                <div class="flex w-full flex-1 items-end">
                                    <div
                                        class="w-full rounded-t {{ $day['isToday'] ? 'bg-emerald-500' : 'bg-emerald-100' }}"
                                        style="height: {{ $day['hours'] > 0 ? max(6, ($day['hours'] / $maxHours) * 100) : 2 }}%"
                                    ></div>
                                </div>
                                <span class="text-xs font-medium {{ $day['isToday'] ? 'text-emerald-600' : 'text-slate-400' }}">{{ $day['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            @if ($upcomingLeave->isNotEmpty())
                <x-card>
                    <p class="mb-4 text-sm font-semibold text-slate-900">Upcoming time off</p>
                    <div class="flex flex-col divide-y divide-slate-100">
                        @foreach ($upcomingLeave as $request)
                            <div class="flex items-center justify-between py-2.5">
                                <div class="flex items-center gap-x-3">
                                    <x-avatar :name="$request->employee->fullName()" size="sm" />
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

            @if ($activity->isNotEmpty())
                <x-card>
                    <p class="mb-4 text-sm font-semibold text-slate-900">Recent activity</p>
                    <div class="flex flex-col">
                        @foreach ($activity as $item)
                            <div class="flex items-start gap-x-3 border-b border-slate-100 py-3 last:border-b-0 last:pb-0">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100">
                                    <i class="bx {{ $item['icon'] }} text-base text-emerald-600"></i>
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
        </div>

        <div class="flex flex-col gap-6">
            <x-card>
                @php
                    $month = now();
                    $firstDayOfMonth = $month->copy()->startOfMonth();
                    $startOffset = $firstDayOfMonth->dayOfWeekIso - 1;
                @endphp
                <p class="mb-4 text-sm font-semibold text-slate-900">{{ $month->format('F Y') }}</p>
                <div class="grid grid-cols-7 gap-y-1 text-center text-xs">
                    @foreach (['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $dayLabel)
                        <div class="py-1 font-medium text-slate-400">{{ $dayLabel }}</div>
                    @endforeach
                    @for ($i = 0; $i < $startOffset; $i++)
                        <div></div>
                    @endfor
                    @for ($day = 1; $day <= $month->daysInMonth; $day++)
                        @php
                            $date = $firstDayOfMonth->copy()->addDays($day - 1);
                            $isToday = $date->isToday();
                            $hasLeave = $myLeaveDatesThisMonth->contains($date->toDateString());
                        @endphp
                        <div class="flex items-center justify-center py-0.5">
                            <span @class([
                                'flex h-7 w-7 items-center justify-center rounded-full',
                                'bg-emerald-500 font-semibold text-white' => $isToday,
                                'bg-emerald-50 text-emerald-700' => $hasLeave && ! $isToday,
                                'text-slate-600' => ! $isToday && ! $hasLeave,
                            ])>{{ $day }}</span>
                        </div>
                    @endfor
                </div>
            </x-card>

            @if ($departmentHeadcount->isNotEmpty())
                <x-card>
                    <p class="mb-4 text-sm font-semibold text-slate-900">Headcount by department</p>
                    <div class="flex flex-col items-center gap-y-4">
                        <div
                            class="h-32 w-32 shrink-0 rounded-full"
                            style="background: radial-gradient(circle, white 42%, transparent 43%), conic-gradient({{ $donutSegments->map(fn ($s) => "{$s['color']} {$s['from']}% {$s['to']}%")->implode(', ') }})"
                        ></div>
                        <div class="flex w-full flex-col gap-y-2">
                            @foreach ($departmentHeadcount as $i => $row)
                                <div class="flex items-center gap-x-2 text-sm">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $donutColors[$i % count($donutColors)] }}"></span>
                                    <span class="truncate text-slate-700">{{ $row->department }}</span>
                                    <span class="ml-auto text-slate-400">{{ $row->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</x-layouts.app>

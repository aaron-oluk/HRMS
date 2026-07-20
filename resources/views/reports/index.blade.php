<x-layouts.app title="Reports" header="Reports">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['route' => 'reports.headcount-by-department', 'icon' => 'bx-group', 'title' => 'Headcount by department', 'description' => 'Active employee count per department.'],
            ['route' => 'reports.leave-utilization', 'icon' => 'bx-calendar-check', 'title' => 'Leave utilization', 'description' => 'Entitled vs. used leave days per employee.'],
            ['route' => 'reports.attendance-summary', 'icon' => 'bx-time-five', 'title' => 'Attendance summary', 'description' => 'Worked hours per employee over a date range.'],
            ['route' => 'reports.payroll-cost-summary', 'icon' => 'bx-receipt', 'title' => 'Payroll cost summary', 'description' => 'Gross/net pay and statutory totals per period.'],
            ['route' => 'reports.recruitment-pipeline', 'icon' => 'bx-briefcase-alt-2', 'title' => 'Recruitment pipeline', 'description' => 'Candidates by stage, requisitions by status.'],
        ] as $report)
            <a href="{{ route($report['route']) }}">
                <x-card class="h-full transition hover:shadow-md">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                        <i class="bx {{ $report['icon'] }} text-xl text-emerald-600"></i>
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $report['title'] }}</h3>
                    <p class="mt-1.5 text-sm text-slate-500">{{ $report['description'] }}</p>
                </x-card>
            </a>
        @endforeach
    </div>
</x-layouts.app>

@php($statusColor = match ($payrollRun->status) {
    'draft' => 'neutral',
    'pending_approval' => 'warning',
    'approved' => 'info',
    'disbursed' => 'success',
    default => 'neutral',
})

<x-layouts.app title="Payroll run" :header="$payrollRun->entity->name.' — '.$payrollRun->period_month->format('F Y')">
    <div class="mb-6 flex items-center justify-between">
        <x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $payrollRun->status)) }}</x-badge>

        <div class="flex gap-x-3">
            @can('payroll.run')
                @if ($payrollRun->status === 'draft')
                    <form method="POST" action="{{ route('payroll.runs.submit', $payrollRun) }}">
                        @csrf
                        <x-button type="submit">Submit for approval</x-button>
                    </form>
                @endif
            @endcan

            @can('payroll.approve')
                @if ($payrollRun->status === 'pending_approval')
                    <form method="POST" action="{{ route('payroll.runs.approve', $payrollRun) }}">
                        @csrf
                        <x-button type="submit">Approve</x-button>
                    </form>
                @endif
            @endcan

            @can('payroll.run')
                @if ($payrollRun->status === 'approved')
                    <form method="POST" action="{{ route('payroll.runs.disburse', $payrollRun) }}">
                        @csrf
                        <x-button type="submit">Mark as disbursed</x-button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    @if ($lineDetail)
        <x-card class="!p-0 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Employee</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Basic salary</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">PAYE</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">NSSF (employee)</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Net pay</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($lines as $line)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $line->employee->fullName() }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">{{ number_format($line->basic_salary) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">{{ number_format($line->paye_amount) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">{{ number_format($line->nssf_employee_amount) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-slate-900">{{ number_format($line->net_pay) }} {{ $line->currency }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No employees on this payroll run.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-card>
                <p class="text-sm text-slate-500">Headcount</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $summary['headcount'] }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-slate-500">Gross pay (team)</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($summary['gross_pay']) }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-slate-500">Net pay (team)</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($summary['net_pay']) }}</p>
            </x-card>
        </div>
        <p class="mt-4 text-sm text-slate-500">Individual salary lines aren't shown at this access level — only team-wide totals.</p>
    @endif
</x-layouts.app>

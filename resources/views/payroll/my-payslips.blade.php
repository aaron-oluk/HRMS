<x-layouts.app title="My Payslips" header="My Payslips">
    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Period</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Gross pay</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">PAYE</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">NSSF</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Other deductions</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Net pay</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($payslips as $line)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $line->payrollRun->period_month->format('F Y') }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format($line->gross_pay) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format($line->paye_amount) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format($line->nssf_employee_amount) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">
                            {{ number_format($line->other_deductions) }}
                            @if ($line->deductions->isNotEmpty())
                                <span class="block text-xs text-slate-400">{{ $line->deductions->pluck('label')->join(', ') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-slate-900">{{ number_format($line->net_pay) }} {{ $line->currency }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No payslips yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.app>

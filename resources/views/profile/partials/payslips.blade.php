<x-card class="!p-0 overflow-hidden">
    <div class="flex items-center gap-x-4 border-b border-slate-100 p-6 pb-5">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
            <i class="bx bx-receipt text-xl text-emerald-600"></i>
        </span>
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Payslips</h3>
            <p class="mt-1 text-sm text-slate-500">Your payslips from every disbursed payroll run.</p>
        </div>
    </div>

    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left font-medium text-slate-500">Period</th>
                <th class="px-6 py-3 text-right font-medium text-slate-500">Gross pay</th>
                <th class="px-6 py-3 text-right font-medium text-slate-500">PAYE</th>
                <th class="px-6 py-3 text-right font-medium text-slate-500">NSSF</th>
                <th class="px-6 py-3 text-right font-medium text-slate-500">Net pay</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($payslips as $line)
                <tr>
                    <td class="px-6 py-3 font-medium text-slate-900">{{ $line->payrollRun->period_month->format('F Y') }}</td>
                    <td class="px-6 py-3 text-right text-slate-500">{{ number_format($line->gross_pay) }}</td>
                    <td class="px-6 py-3 text-right text-slate-500">{{ number_format($line->paye_amount) }}</td>
                    <td class="px-6 py-3 text-right text-slate-500">{{ number_format($line->nssf_employee_amount) }}</td>
                    <td class="px-6 py-3 text-right font-medium text-slate-900">{{ number_format($line->net_pay) }} {{ $line->currency }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-slate-500">No payslips yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-card>

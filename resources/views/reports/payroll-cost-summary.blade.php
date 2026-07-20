<x-layouts.app title="Payroll cost summary" header="Payroll cost summary">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('reports.payroll-cost-summary', ['format' => 'csv']) }}"><x-button variant="secondary" icon="bx-download">Export CSV</x-button></a>
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Period</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Headcount</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Gross pay</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">PAYE</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">NSSF (employee)</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Net pay</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ \Illuminate\Support\Carbon::parse($row->period_month)->format('F Y') }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ $row->headcount }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format($row->gross_pay) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format($row->paye) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format($row->nssf_employee) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-slate-900">{{ number_format($row->net_pay) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No payroll runs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.app>

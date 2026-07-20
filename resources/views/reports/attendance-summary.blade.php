<x-layouts.app title="Attendance summary" header="Attendance summary">
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-x-2">
            <x-input type="date" name="start_date" :value="$start" onchange="this.form.submit()" />
            <span class="text-sm text-slate-500">to</span>
            <x-input type="date" name="end_date" :value="$end" onchange="this.form.submit()" />
        </form>
        <a href="{{ route('reports.attendance-summary', ['start_date' => $start, 'end_date' => $end, 'format' => 'csv']) }}"><x-button variant="secondary" icon="bx-download">Export CSV</x-button></a>
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Employee</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Hours worked</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $row['employee'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ $row['hours'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-slate-500">No data for this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.app>

<x-layouts.app title="Leave utilization" header="Leave utilization">
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-x-2">
            <x-select name="year" onchange="this.form.submit()">
                @foreach (range(now()->year, now()->year - 3) as $y)
                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endforeach
            </x-select>
        </form>
        <a href="{{ route('reports.leave-utilization', ['year' => $year, 'format' => 'csv']) }}"><x-button variant="secondary" icon="bx-download">Export CSV</x-button></a>
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Employee</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Entitled</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Used</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $row['employee'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ $row['entitled'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ $row['used'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.app>

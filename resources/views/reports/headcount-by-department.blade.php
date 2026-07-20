<x-layouts.app title="Headcount by department" header="Headcount by department">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('reports.headcount-by-department', ['format' => 'csv']) }}"><x-button variant="secondary" icon="bx-download">Export CSV</x-button></a>
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Department</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-500">Headcount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $row->department }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ $row->headcount }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-slate-500">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.app>

<x-layouts.app title="Headcount by department" header="Headcount by department">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('reports.headcount-by-department', ['format' => 'csv']) }}"><x-button variant="secondary" icon="bx-download">Export CSV</x-button></a>
    </div>

    <x-card class="mb-6">
        <x-bar-chart :data="$chartData" />
    </x-card>

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

    @if ($selectedDepartment)
        <x-card class="mt-6 !p-0 overflow-x-auto">
            <div class="flex items-center justify-between border-b border-slate-100 p-4">
                <h3 class="text-sm font-semibold text-slate-900">Employees in {{ $selectedDepartment->name }}</h3>
                <a href="{{ route('reports.headcount-by-department') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">Clear filter</a>
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Employee number</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Position</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($departmentEmployees as $employee)
                        <tr>
                            <td class="px-4 py-3 text-slate-500">{{ $employee->employee_number }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <a href="{{ route('employees.show', $employee) }}" class="hover:text-emerald-600">{{ $employee->fullName() }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $employee->currentEmployment?->position?->title ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">No employees in this department.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    @endif
</x-layouts.app>

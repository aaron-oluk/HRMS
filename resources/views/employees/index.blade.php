<x-layouts.app title="Employees" header="Employees">
    <div class="mb-4 flex justify-end">
        @can('employees.manage')
            <a href="{{ route('employees.create') }}"><x-button>Add employee</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Employee #</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Position</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($employees as $employee)
                    <tr class="cursor-pointer hover:bg-slate-50" onclick="window.location='{{ route('employees.show', $employee) }}'">
                        <td class="px-4 py-3 text-slate-500">{{ $employee->employee_number }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $employee->fullName() }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $employee->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $employee->currentEmployment?->position?->title ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst(str_replace('_', ' ', $employee->status)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No employees yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $employees->links() }}</div>
</x-layouts.app>

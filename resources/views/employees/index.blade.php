<x-layouts.app title="Employees" header="Employees">
    <div class="mb-4 flex items-center justify-between gap-x-4">
        <form method="GET" class="w-full max-w-xs">
            <x-search-input name="q" :value="$search" placeholder="Search by name or employee #" />
        </form>
        @can('employees.create')
            <a href="{{ route('employees.create') }}"><x-button icon="bx-plus">Add employee</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
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
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <div class="flex items-center gap-x-3">
                                <x-avatar :name="$employee->fullName()" size="sm" />
                                {{ $employee->fullName() }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $employee->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $employee->currentEmployment?->position?->title ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColor = match ($employee->status) {
                                    'active' => 'success',
                                    'on_leave' => 'info',
                                    'suspended' => 'danger',
                                    default => 'neutral',
                                };
                            @endphp
                            <x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $employee->status)) }}</x-badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">{{ $search !== '' ? 'No employees match your search.' : 'No employees yet.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $employees->links() }}</div>
</x-layouts.app>

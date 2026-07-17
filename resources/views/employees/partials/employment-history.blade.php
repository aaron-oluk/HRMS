<div class="mb-4 flex justify-end">
    @can('employments.manage')
        <a href="{{ route('employees.employments.create', $employee) }}">
            <x-button>Record employment change</x-button>
        </a>
    @endcan
</div>

<x-card class="!p-0 overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Effective from</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Effective to</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Position</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Department</th>
                @can('employees.view-sensitive')
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Salary</th>
                @endcan
                <th class="px-4 py-3 text-left font-medium text-slate-500">Reason</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($employee->employments as $employment)
                <tr>
                    <td class="px-4 py-3 text-slate-500">{{ $employment->effective_from->toDateString() }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $employment->effective_to?->toDateString() ?? 'Current' }}</td>
                    <td class="px-4 py-3 text-slate-900">{{ $employment->position->title }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $employment->department->name }}</td>
                    @can('employees.view-sensitive')
                        <td class="px-4 py-3 text-slate-500">{{ number_format($employment->basic_salary) }} {{ $employment->currency }}</td>
                    @endcan
                    <td class="px-4 py-3 text-slate-500">{{ ucfirst(str_replace('_', ' ', $employment->reason)) }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ ucfirst($employment->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-slate-500">No employment records yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-card>

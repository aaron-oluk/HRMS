<x-layouts.app title="Leave Types" header="Leave Types">
    <div class="mb-4 flex justify-end">
        @can('leave.manage-types')
            <a href="{{ route('leave-types.create') }}"><x-button icon="bx-plus">Add leave type</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Days/year</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Paid</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Approval required</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($leaveTypes as $leaveType)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $leaveType->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $leaveType->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $leaveType->default_days_per_year }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $leaveType->is_paid ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $leaveType->requires_approval ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst($leaveType->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('leave.manage-types')
                                <a href="{{ route('leave-types.edit', $leaveType) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">No leave types yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $leaveTypes->links() }}</div>
</x-layouts.app>

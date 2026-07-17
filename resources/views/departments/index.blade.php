<x-layouts.app title="Departments" header="Departments">
    <div class="mb-4 flex justify-end">
        @can('org.manage')
            <a href="{{ route('departments.create') }}"><x-button icon="bx-plus">Add department</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Parent</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($departments as $department)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $department->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $department->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $department->parent?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst($department->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('org.manage')
                                <a href="{{ route('departments.edit', $department) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No departments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $departments->links() }}</div>
</x-layouts.app>

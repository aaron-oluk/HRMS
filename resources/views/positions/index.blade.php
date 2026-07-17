<x-layouts.app title="Positions" header="Positions">
    <div class="mb-4 flex justify-end">
        @can('org.manage')
            <a href="{{ route('positions.create') }}"><x-button icon="bx-plus">Add position</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Title</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Department</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($positions as $position)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $position->title }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $position->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $position->department?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst($position->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('org.manage')
                                <a href="{{ route('positions.edit', $position) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No positions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $positions->links() }}</div>
</x-layouts.app>

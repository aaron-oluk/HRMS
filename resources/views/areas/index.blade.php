<x-layouts.app title="Areas" header="Areas">
    <div class="mb-4 flex justify-end">
        @can('org.manage')
            <a href="{{ route('areas.create') }}"><x-button icon="bx-plus">Add area</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Code</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Branches</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($areas as $area)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $area->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $area->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $area->code }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $area->branches()->count() }}</td>
                        <td class="px-4 py-3"><x-badge :color="$area->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($area->status) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            @can('org.manage')
                                <a href="{{ route('areas.edit', $area) }}" class="text-emerald-600 hover:text-emerald-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No areas yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $areas->links() }}</div>
</x-layouts.app>

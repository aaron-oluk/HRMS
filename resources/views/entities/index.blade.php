<x-layouts.app title="Entities" header="Entities">
    <div class="mb-4 flex justify-end">
        @can('org.manage')
            <a href="{{ route('entities.create') }}">
                <x-button icon="bx-plus">Add entity</x-button>
            </a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Registration No.</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Currency</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($entities as $entity)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $entity->registration_number }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $entity->currency }}</td>
                        <td class="px-4 py-3"><x-badge :color="$entity->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($entity->status) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            @can('org.manage')
                                <a href="{{ route('entities.edit', $entity) }}" class="text-emerald-600 hover:text-emerald-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No entities yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $entities->links() }}</div>
</x-layouts.app>

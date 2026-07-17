<x-layouts.app title="Branches" header="Branches">
    <div class="mb-4 flex justify-end">
        @can('org.manage')
            <a href="{{ route('branches.create') }}"><x-button icon="bx-plus">Add branch</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Code</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($branches as $branch)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $branch->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $branch->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $branch->code }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst($branch->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('org.manage')
                                <a href="{{ route('branches.edit', $branch) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No branches yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $branches->links() }}</div>
</x-layouts.app>

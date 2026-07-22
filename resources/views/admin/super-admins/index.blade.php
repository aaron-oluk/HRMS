<x-layouts.admin title="Platform admins" header="Platform admins">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.super-admins.create') }}"><x-button icon="bx-plus">Add platform admin</x-button></a>
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Access</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Added</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($superAdmins as $superAdmin)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $superAdmin->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $superAdmin->email }}</td>
                        <td class="px-4 py-3">
                            @if ($superAdmin->is_super_admin)
                                <x-badge color="info">Global</x-badge>
                            @else
                                <x-badge color="neutral">Org Admin &middot; {{ $superAdmin->assignedTenants->pluck('name')->join(', ') ?: 'no companies' }}</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $superAdmin->created_at->toFormattedDateString() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No platform admins yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $superAdmins->links() }}</div>
</x-layouts.admin>

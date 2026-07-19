<x-layouts.admin title="Companies" header="Companies">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.tenants.create') }}"><x-button icon="bx-plus">Onboard a company</x-button></a>
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Company</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Currency</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Users</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Employees</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Onboarded</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tenants as $tenant)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-900">{{ $tenant->name }}</p>
                            <p class="text-slate-500">{{ $tenant->slug }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $tenant->currency }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $tenant->users_count }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $tenant->employees_count }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$tenant->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($tenant->status) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $tenant->created_at->toFormattedDateString() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No companies onboarded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $tenants->links() }}</div>
</x-layouts.admin>

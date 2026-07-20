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
                    <th class="px-4 py-3"></th>
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
                            <x-badge :color="$tenant->status === 'active' ? 'success' : 'danger'">{{ ucfirst($tenant->status) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $tenant->created_at->toFormattedDateString() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-x-3">
                                <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-emerald-600 hover:text-emerald-500">View</a>
                                <a href="{{ route('admin.tenants.edit', $tenant) }}" class="text-slate-500 hover:text-slate-700">Edit</a>
                                @if ($tenant->status === 'active')
                                    <form method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}" onsubmit="return confirm('Suspend {{ $tenant->name }}? Its users will be signed out immediately.')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-500">Suspend</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.tenants.reactivate', $tenant) }}">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:text-emerald-500">Reactivate</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">No companies onboarded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $tenants->links() }}</div>
</x-layouts.admin>

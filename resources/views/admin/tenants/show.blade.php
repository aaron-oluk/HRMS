@php($statusColor = $tenant->status === 'active' ? 'success' : 'danger')

<x-layouts.admin title="Company" :header="$tenant->name">
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-x-3">
            <x-badge :color="$statusColor">{{ ucfirst($tenant->status) }}</x-badge>
            <span class="text-sm text-slate-500">{{ $tenant->slug }} &middot; {{ $tenant->currency }} &middot; {{ $tenant->timezone }}</span>
        </div>
        <div class="flex gap-x-3">
            <a href="{{ route('admin.tenants.edit', $tenant) }}"><x-button variant="secondary" icon="bx-edit">Edit</x-button></a>
            @if ($hrAdmins->isNotEmpty())
                <form method="POST" action="{{ route('admin.tenants.impersonate', $tenant) }}">
                    @csrf
                    <x-button type="submit" icon="bx-log-in">Log in as HR Admin</x-button>
                </form>
            @endif
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
        <x-card>
            <p class="text-sm text-slate-500">Users</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $tenant->users_count }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-slate-500">Employees</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $tenant->employees_count }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-slate-500">Entities</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $tenant->entities_count }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-slate-500">Departments</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $departmentCount }}</p>
        </x-card>
    </div>

    <x-card class="mb-6">
        <h3 class="text-sm font-semibold text-slate-900">Enabled modules</h3>
        <p class="mt-0.5 text-sm text-slate-500">Turn optional modules on or off for this company. Core modules (Employees, Time Off, Attendance) are always on.</p>

        <form method="POST" action="{{ route('admin.tenants.modules.update', $tenant) }}" class="mt-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($modules as $module)
                    <label class="flex items-center gap-x-2 text-sm text-slate-700">
                        <x-checkbox name="modules[]" value="{{ $module }}" @checked($tenant->hasModule($module)) />
                        {{ ucfirst(str_replace('esignature', 'e-signature', $module)) }}
                    </label>
                @endforeach
            </div>

            <div class="mt-4 border-t border-slate-100 pt-4">
                <x-button type="submit" variant="secondary">Save modules</x-button>
            </div>
        </form>
    </x-card>

    <x-card class="!p-0 overflow-x-auto">
        <div class="border-b border-slate-100 p-4">
            <h3 class="text-sm font-semibold text-slate-900">Users</h3>
        </div>
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Role(s)</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->created_at->toFormattedDateString() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No users yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.admin>

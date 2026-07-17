<x-layouts.app title="Users & Roles" header="Users & Roles">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('users.create') }}"><x-button>Add user</x-button></a>
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Role</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">2FA</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->getRoleNames()->first() ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->two_factor_confirmed_at ? 'Enabled' : 'Disabled' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst($user->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No users yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.app>

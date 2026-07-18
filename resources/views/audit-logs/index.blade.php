<x-layouts.app title="Audit Log" header="Audit Log">
    <p class="mb-6 text-sm text-slate-500">Login attempts, denied access, sensitive-field views, and role changes across the tenant.</p>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Actor</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Action</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Subject</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">IP address</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-slate-900">{{ $log->actor?->name ?? 'Unknown' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ class_basename($log->auditable_type) }}
                            @if ($log->field)
                                · {{ $log->field }}
                            @endif
                            @if ($log->new_value)
                                · {{ \Illuminate\Support\Str::limit($log->new_value, 60) }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No access events recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.app>

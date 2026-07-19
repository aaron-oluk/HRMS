<x-layouts.app title="Payroll" header="Payroll">
    <div class="mb-4 flex justify-end">
        @can('payroll.run')
            <a href="{{ route('payroll.runs.create') }}"><x-button icon="bx-plus">Generate run</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Entity</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Period</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($runs as $run)
                    @php($statusColor = match ($run->status) {
                        'draft' => 'neutral',
                        'pending_approval' => 'warning',
                        'approved' => 'info',
                        'disbursed' => 'success',
                        default => 'neutral',
                    })
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $run->entity->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $run->period_month->format('F Y') }}</td>
                        <td class="px-4 py-3"><x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $run->status)) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('payroll.runs.show', $run) }}" class="text-emerald-600 hover:text-emerald-500">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No payroll runs yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $runs->links() }}</div>
</x-layouts.app>

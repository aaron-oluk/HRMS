<x-layouts.app title="Recruitment" header="Recruitment">
    @include('recruitment.partials.header', ['activeTab' => 'job-postings'])

    <div class="mb-4 flex justify-end">
        @can('recruitment.manage')
            <a href="{{ route('recruitment.requisitions.create') }}"><x-button icon="bx-plus">New role to hire for</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Title</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Type</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Department</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Positions</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($requisitions as $requisition)
                    @php($statusColor = match ($requisition->status) {
                        'open' => 'success',
                        'on_hold' => 'warning',
                        'filled' => 'info',
                        'closed' => 'neutral',
                        default => 'neutral',
                    })
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $requisition->title }}</td>
                        <td class="px-4 py-3"><x-badge color="info">{{ ucfirst($requisition->type) }}</x-badge></td>
                        <td class="px-4 py-3 text-slate-500">{{ $requisition->department->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $requisition->headcount }}</td>
                        <td class="px-4 py-3"><x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $requisition->status)) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('recruitment.pipeline', ['job_requisition_id' => $requisition->id]) }}" class="text-emerald-600 hover:text-emerald-500">Candidates</a>
                            @can('recruitment.manage')
                                &middot;
                                <a href="{{ route('recruitment.requisitions.edit', $requisition) }}" class="text-emerald-600 hover:text-emerald-500">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No roles to hire for yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $requisitions->links() }}</div>
</x-layouts.app>

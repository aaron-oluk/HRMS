<x-layouts.app title="Recruitment pipeline" header="Recruitment pipeline">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('reports.recruitment-pipeline', ['format' => 'csv']) }}"><x-button variant="secondary" icon="bx-download">Export CSV</x-button></a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-card>
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Candidates by stage</h3>
            <x-bar-chart :data="$chartData" />
        </x-card>

        <x-card>
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Requisitions by status</h3>
            <dl class="space-y-2 text-sm">
                @foreach (['draft', 'open', 'on_hold', 'closed', 'filled'] as $status)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">{{ ucfirst(str_replace('_', ' ', $status)) }}</dt>
                        <dd class="font-medium text-slate-900">{{ $byRequisitionStatus[$status] ?? 0 }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>
    </div>

    @if ($selectedStage)
        <x-card class="mt-6 !p-0 overflow-x-auto">
            <div class="flex items-center justify-between border-b border-slate-100 p-4">
                <h3 class="text-sm font-semibold text-slate-900">Candidates in {{ ucfirst(str_replace('_', ' ', $selectedStage)) }}</h3>
                <a href="{{ route('reports.recruitment-pipeline') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">Clear filter</a>
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                        @can('recruitment.view-candidate-pii')
                            <th class="px-4 py-3 text-left font-medium text-slate-500">Email</th>
                        @endcan
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Requisition</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Source</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Applied</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($stageCandidates as $candidate)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $candidate->fullName() }}</td>
                            @can('recruitment.view-candidate-pii')
                                <td class="px-4 py-3 text-slate-500">{{ $candidate->email }}</td>
                            @endcan
                            <td class="px-4 py-3 text-slate-500">{{ $candidate->jobRequisition->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $candidate->source ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $candidate->created_at->toFormattedDateString() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No candidates in this stage.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    @endif
</x-layouts.app>

<x-layouts.app title="Recruitment pipeline" header="Recruitment pipeline">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('reports.recruitment-pipeline', ['format' => 'csv']) }}"><x-button variant="secondary" icon="bx-download">Export CSV</x-button></a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-card>
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Candidates by stage</h3>
            <dl class="space-y-2 text-sm">
                @foreach (\App\Models\Candidate::STATUSES as $status)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">{{ ucfirst($status) }}</dt>
                        <dd class="font-medium text-slate-900">{{ $byStage[$status] ?? 0 }}</dd>
                    </div>
                @endforeach
            </dl>
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
</x-layouts.app>

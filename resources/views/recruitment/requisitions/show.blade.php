@php($statusColor = match ($jobRequisition->status) {
    'open' => 'success',
    'on_hold' => 'warning',
    'filled' => 'info',
    'closed' => 'neutral',
    default => 'neutral',
})

<x-layouts.app title="Requisition" :header="$jobRequisition->title">
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-x-3">
            <x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $jobRequisition->status)) }}</x-badge>
            <x-badge color="info">{{ ucfirst($jobRequisition->type) }}</x-badge>
            <span class="text-sm text-slate-500">{{ $jobRequisition->department->name }} &middot; {{ $jobRequisition->position->title }} &middot; {{ $jobRequisition->headcount }} opening(s)</span>
        </div>

        @can('recruitment.manage')
            <a href="{{ route('recruitment.requisitions.edit', $jobRequisition) }}"><x-button variant="secondary" icon="bx-edit">Edit</x-button></a>
        @endcan
    </div>

    @if ($jobRequisition->description)
        <x-card class="mb-6">
            <p class="text-sm text-slate-600">{{ $jobRequisition->description }}</p>
        </x-card>
    @endif

    <x-card class="!p-0 overflow-x-auto">
        <div class="flex items-center justify-between border-b border-slate-100 p-4">
            <h3 class="text-sm font-semibold text-slate-900">Candidates</h3>
            @can('recruitment.manage')
                <button type="button" @click="$refs.addCandidate.classList.toggle('hidden')" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">
                    + Add candidate
                </button>
            @endcan
        </div>

        @can('recruitment.manage')
            <form x-ref="addCandidate" method="POST" action="{{ route('recruitment.requisitions.candidates.store', $jobRequisition) }}" class="hidden grid grid-cols-1 gap-4 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-5">
                @csrf
                <x-input name="first_name" placeholder="First name" required />
                <x-input name="last_name" placeholder="Last name" required />
                <x-input name="email" type="email" placeholder="Email" required />
                <x-input name="phone" placeholder="Phone" />
                <x-input name="source" placeholder="Source (e.g. referral)" />
                <div class="sm:col-span-2 lg:col-span-5">
                    <x-button type="submit" icon="bx-plus">Add candidate</x-button>
                </div>
            </form>
        @endcan

        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                    @can('recruitment.view-candidate-pii')
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Contact</th>
                    @endcan
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Source</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Stage</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($candidates as $candidate)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <a href="{{ route('recruitment.candidates.show', $candidate) }}" class="hover:text-emerald-600">{{ $candidate->fullName() }}</a>
                        </td>
                        @can('recruitment.view-candidate-pii')
                            <td class="px-4 py-3 text-slate-500">
                                {{ $candidate->email }}
                                @if ($candidate->phone)
                                    <br>{{ $candidate->phone }}
                                @endif
                            </td>
                        @endcan
                        <td class="px-4 py-3 text-slate-500">{{ $candidate->source ?? '—' }}</td>
                        @php($stageColor = match ($candidate->status) {
                            'contracts_and_appointments', 'probation' => 'success',
                            'rejected' => 'danger',
                            default => 'neutral',
                        })
                        <td class="px-4 py-3"><x-badge :color="$stageColor">{{ ucfirst(str_replace('_', ' ', $candidate->status)) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            @can('recruitment.manage')
                                <form method="POST" action="{{ route('recruitment.requisitions.candidates.stage', [$jobRequisition, $candidate]) }}" class="flex items-center justify-end gap-x-2">
                                    @csrf
                                    <x-select name="status" onchange="this.form.submit()" class="!py-1 !text-xs">
                                        @foreach (\App\Models\Candidate::STATUSES as $status)
                                            <option value="{{ $status }}" @selected($candidate->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </x-select>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No candidates yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.app>

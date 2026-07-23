@php
    $stageLabel = fn (string $stage) => ucfirst(str_replace('_', ' ', $stage));
@endphp

<x-layouts.app title="Recruitment" header="Recruitment">
    <div
        x-data="{
            showAddCandidateModal: {{ $errors->hasAny(['job_requisition_id', 'first_name', 'last_name', 'email', 'phone', 'source']) ? 'true' : 'false' }},
            draggingId: null,
            dragOverStage: null,
            onDrop(stage) {
                this.dragOverStage = null;
                if (! this.draggingId) return;
                const form = document.getElementById('stage-form-' + this.draggingId);
                if (form) {
                    form.querySelector('input[name=status]').value = stage;
                    form.submit();
                }
                this.draggingId = null;
            },
        }"
    >
        @include('recruitment.partials.header', ['activeTab' => 'pipeline'])

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-x-3">
                <form method="GET" action="{{ route('recruitment.pipeline') }}">
                    <x-select name="job_requisition_id" onchange="this.form.submit()">
                        <option value="">All jobs</option>
                        @foreach ($requisitions as $requisition)
                            <option value="{{ $requisition->id }}" @selected($selectedJobRequisitionId === $requisition->id)>{{ $requisition->title }}</option>
                        @endforeach
                    </x-select>
                </form>
                <span class="text-sm text-slate-500">{{ $totalCandidates }} {{ $totalCandidates === 1 ? 'candidate' : 'candidates' }}</span>
            </div>

            @can('recruitment.manage')
                <x-button icon="bx-plus" type="button" @click="showAddCandidateModal = true">Add Candidate</x-button>
            @endcan
        </div>

        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach (\App\Models\Candidate::STATUSES as $stage)
                @php($stageCandidates = $candidatesByStage->get($stage, collect()))
                <div class="w-72 shrink-0">
                    <div class="mb-3 flex items-center gap-x-2">
                        <x-stage-dot :stage="$stage" />
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $stageLabel($stage) }}</h3>
                        <span class="text-xs text-slate-400">{{ $stageCandidates->count() }}</span>
                    </div>

                    <div
                        class="min-h-[120px] space-y-2 rounded-lg bg-slate-100/70 p-2 transition"
                        :class="dragOverStage === '{{ $stage }}' ? 'bg-emerald-50 ring-2 ring-emerald-200' : ''"
                        @dragover.prevent="dragOverStage = '{{ $stage }}'"
                        @dragleave="dragOverStage = null"
                        @drop.prevent="onDrop('{{ $stage }}')"
                    >
                        @forelse ($stageCandidates as $candidate)
                            <div
                                draggable="true"
                                @dragstart="draggingId = {{ $candidate->id }}"
                                @dragend="draggingId = null"
                                class="cursor-grab rounded-lg bg-white p-3 shadow-sm transition hover:shadow-md active:cursor-grabbing"
                            >
                                <a href="{{ route('recruitment.candidates.show', $candidate) }}" class="block">
                                    <p class="text-sm font-semibold text-slate-900">{{ $candidate->fullName() }}</p>
                                    @can('recruitment.view-candidate-pii')
                                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ $candidate->email }}</p>
                                    @endcan
                                    @if ($candidate->source)
                                        <p class="mt-1 text-xs text-slate-400">via {{ $candidate->source }}</p>
                                    @endif
                                    @if ($candidate->rating)
                                        <p class="mt-1.5 flex items-center gap-x-0.5 text-amber-500">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="bx {{ $i <= $candidate->rating ? 'bxs-star' : 'bx-star text-slate-200' }} text-xs"></i>
                                            @endfor
                                        </p>
                                    @endif
                                </a>
                            </div>
                            <form id="stage-form-{{ $candidate->id }}" method="POST" action="{{ route('recruitment.requisitions.candidates.stage', [$candidate->jobRequisition, $candidate]) }}" class="hidden">
                                @csrf
                                <input type="hidden" name="status" value="">
                            </form>
                        @empty
                            <p class="px-2 py-6 text-center text-sm text-slate-400">No candidates</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Add Candidate modal --}}
        @can('recruitment.manage')
            <div x-show="showAddCandidateModal" x-cloak class="dialog-backdrop fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="showAddCandidateModal = false">
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.outside="showAddCandidateModal = false">
                    <h2 class="text-base font-semibold text-slate-900">Add Candidate</h2>
                    <p class="mt-1 text-sm text-slate-500">Add a new candidate to a job posting's pipeline.</p>

                    <form method="POST" action="{{ route('recruitment.candidates.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-label for="candidate_job" value="Job posting" />
                            <x-select id="candidate_job" name="job_requisition_id" class="mt-1" required>
                                <option value="">Select a job posting</option>
                                @foreach ($openRequisitions as $requisition)
                                    <option value="{{ $requisition->id }}">{{ $requisition->title }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('job_requisition_id')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-label for="candidate_first_name" value="First name" />
                                <x-input id="candidate_first_name" name="first_name" required class="mt-1" />
                                <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                            </div>
                            <div>
                                <x-label for="candidate_last_name" value="Last name" />
                                <x-input id="candidate_last_name" name="last_name" required class="mt-1" />
                                <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                            </div>
                        </div>

                        <div>
                            <x-label for="candidate_email" value="Email" />
                            <x-input id="candidate_email" name="email" type="email" required class="mt-1" />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-label for="candidate_phone" value="Phone" />
                                <x-input id="candidate_phone" name="phone" class="mt-1" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                            </div>
                            <div>
                                <x-label for="candidate_source" value="Source" />
                                <x-input id="candidate_source" name="source" placeholder="e.g. LinkedIn" class="mt-1" />
                                <x-input-error :messages="$errors->get('source')" class="mt-1" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-x-3 pt-2">
                            <x-button type="button" variant="secondary" @click="showAddCandidateModal = false">Cancel</x-button>
                            <x-button type="submit">Add Candidate</x-button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
</x-layouts.app>

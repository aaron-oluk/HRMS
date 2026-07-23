@php
    $stageLabel = ucfirst(str_replace('_', ' ', $candidate->status));
    $nextStatus = $candidate->nextStatus();
@endphp

<x-layouts.app title="{{ $candidate->fullName() }}" header="Recruitment">
    <div class="mb-4">
        <a href="{{ route('recruitment.pipeline', ['job_requisition_id' => $candidate->job_requisition_id]) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">
            &larr; {{ $candidate->jobRequisition->title }}
        </a>
    </div>

    <div class="mb-6 flex items-center gap-x-3">
        <x-avatar :name="$candidate->fullName()" size="lg" />
        <div>
            <p class="text-lg font-semibold text-slate-900">{{ $candidate->fullName() }}</p>
            <p class="mt-0.5 text-sm text-slate-500">
                @if ($candidate->creator)
                    Added by {{ $candidate->creator->name }} on {{ $candidate->created_at->format('M j, Y') }}
                @else
                    Applied on {{ $candidate->created_at->format('M j, Y') }}
                @endif
                @if ($candidate->source) &middot; from {{ $candidate->source }} @endif
            </p>
        </div>
    </div>

    <div x-data="{ tab: 'overview' }" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex gap-x-6">
                    <button type="button" @click="tab = 'overview'" :class="tab === 'overview' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                        <i class="bx bx-grid-alt text-base"></i> Overview
                    </button>
                    <button type="button" @click="tab = 'notes'" :class="tab === 'notes' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                        <i class="bx bx-note text-base"></i> Notes
                        @if ($candidate->comments->isNotEmpty())
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">{{ $candidate->comments->count() }}</span>
                        @endif
                    </button>
                </nav>
            </div>

            {{-- Overview tab --}}
            <div class="mt-6 space-y-6" x-show="tab === 'overview'">
                <x-card>
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Rating</h3>
                    <div class="flex items-center gap-x-1">
                        @for ($i = 1; $i <= 5; $i++)
                            @can('recruitment.manage')
                                <form method="POST" action="{{ route('recruitment.candidates.rate', $candidate) }}">
                                    @csrf
                                    <input type="hidden" name="rating" value="{{ $i }}">
                                    <button type="submit" class="text-xl {{ $candidate->rating && $i <= $candidate->rating ? 'text-amber-500' : 'text-slate-200 hover:text-amber-300' }}">
                                        <i class="bx {{ $candidate->rating && $i <= $candidate->rating ? 'bxs-star' : 'bx-star' }}"></i>
                                    </button>
                                </form>
                            @else
                                <i class="bx {{ $candidate->rating && $i <= $candidate->rating ? 'bxs-star text-amber-500' : 'bx-star text-slate-200' }} text-xl"></i>
                            @endcan
                        @endfor
                    </div>
                </x-card>

                <x-card>
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Contact</h3>
                    @can('recruitment.view-candidate-pii')
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-xs font-medium uppercase text-slate-400">Email</dt>
                                <dd class="mt-0.5 text-slate-900">{{ $candidate->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-slate-400">Phone</dt>
                                <dd class="mt-0.5 text-slate-900">{{ $candidate->phone ?? '—' }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-slate-500">You don't have permission to view contact details.</p>
                    @endcan
                </x-card>
            </div>

            {{-- Notes tab --}}
            <div class="mt-6" x-show="tab === 'notes'" x-cloak>
                <x-card>
                    @can('recruitment.manage')
                        <form method="POST" action="{{ route('recruitment.candidates.comments.store', $candidate) }}" class="mb-4 space-y-3">
                            @csrf
                            <textarea name="body" rows="3" required placeholder="Leave a note about this candidate..." class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none">{{ old('body') }}</textarea>
                            <x-input-error :messages="$errors->get('body')" />
                            <div class="flex justify-end">
                                <x-button type="submit">Comment</x-button>
                            </div>
                        </form>
                    @endcan

                    @if ($candidate->comments->isEmpty())
                        <p class="text-sm text-slate-500">No notes yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($candidate->comments as $comment)
                                <div class="rounded-md border border-slate-100 p-3">
                                    <div class="flex items-start justify-between gap-x-2">
                                        <p class="text-xs font-medium text-slate-900">{{ $comment->author?->name ?? 'Unknown' }}</p>
                                        <div class="flex items-center gap-x-2">
                                            <p class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</p>
                                            @can('recruitment.manage')
                                                <form method="POST" action="{{ route('recruitment.candidates.comments.destroy', [$candidate, $comment]) }}" onsubmit="return confirm('Delete this note?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-base"></i></button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                    <p class="mt-1.5 text-sm text-slate-600">{{ $comment->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            </div>
        </div>

        {{-- Status card --}}
        <div>
            <x-card>
                <p class="text-sm font-semibold text-slate-900">{{ $candidate->jobRequisition->title }}</p>
                <p class="mt-0.5 flex items-center gap-x-1.5 text-xs text-slate-500">
                    <x-stage-dot :stage="$candidate->status" /> {{ $stageLabel }}
                </p>

                @can('recruitment.manage')
                    <div class="mt-4 flex items-center gap-x-2">
                        @if ($candidate->status !== 'rejected')
                            <form method="POST" action="{{ route('recruitment.requisitions.candidates.stage', [$candidate->jobRequisition, $candidate]) }}" onsubmit="return confirm('Reject this candidate?')">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:border-red-200 hover:text-red-500" title="Reject">
                                    <i class="bx bx-block text-lg"></i>
                                </button>
                            </form>
                        @endif
                        @if ($nextStatus)
                            <form method="POST" action="{{ route('recruitment.requisitions.candidates.stage', [$candidate->jobRequisition, $candidate]) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="status" value="{{ $nextStatus }}">
                                <x-button type="submit" icon="bx-right-arrow-alt" class="w-full justify-center">Proceed</x-button>
                            </form>
                        @endif
                    </div>
                @endcan
            </x-card>
        </div>
    </div>
</x-layouts.app>

@php
    $trend = $performanceReviews
        ->whereNotNull('manager_rating')
        ->sortBy(fn ($review) => $review->cycle->start_date)
        ->map(fn ($review) => ['label' => $review->cycle->name, 'score' => round(((float) $review->manager_rating / 5) * 100, 1)])
        ->values()
        ->all();

    $latestScore = $trend !== [] ? end($trend)['score'] : null;
    $previousScore = count($trend) > 1 ? $trend[count($trend) - 2]['score'] : null;
    $scoreDelta = $latestScore !== null && $previousScore !== null ? round($latestScore - $previousScore, 1) : null;

    $scoreLabel = match (true) {
        $latestScore === null => 'No reviews yet',
        $latestScore >= 90 => 'Excellent',
        $latestScore >= 75 => 'Strong',
        $latestScore >= 60 => 'Solid',
        $latestScore >= 40 => 'Needs improvement',
        default => 'At risk',
    };

    $completedCount = $performanceReviews->where('status', 'completed')->count();
    $activeGoalsCount = $goals->whereNotIn('status', ['completed'])->count();
    $onTrackGoalsCount = $goals->where('status', 'on_track')->count();

    $goalsWithTarget = $goals->filter(fn ($goal) => $goal->target_value);
    $goalsAvgProgress = $goalsWithTarget->isNotEmpty()
        ? $goalsWithTarget->map(fn ($goal) => min(100, ((float) ($goal->current_value ?? 0) / (float) $goal->target_value) * 100))->avg()
        : null;

    $peerAvgRating = $receivedFeedback->isNotEmpty() ? round($receivedFeedback->avg('rating'), 1) : null;
    $peerCount = $receivedFeedback->count();

    $nextOneOnOne = $oneOnOnes->where('status', 'scheduled')->sortBy('scheduled_at')->first();
    $pendingSelfReview = $performanceReviews->firstWhere('status', 'pending');
    $pendingFeedbackToGive = $feedbackRequests->where('status', 'pending');
    $pendingFeedbackCount = $pendingFeedbackToGive->count();

    $defaultTab = match (true) {
        $pendingSelfReview !== null => 'reviews',
        $pendingFeedbackCount > 0 => 'feedback',
        default => 'overview',
    };

    $formatScore = fn (?float $value) => $value !== null ? rtrim(rtrim(number_format($value, 1), '0'), '.') : null;
@endphp

<x-layouts.app title="My Performance" header="My Performance">
    <div
        x-data="{
            tab: '{{ $defaultTab }}',
            showGoalModal: {{ $errors->hasAny(['title', 'target_value', 'unit', 'due_date']) ? 'true' : 'false' }},
        }"
    >
        {{-- Stat cards --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <i class="bx bx-line-chart text-2xl text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Latest score</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">
                        {{ $formatScore($latestScore) ?? '—' }}
                        @if ($latestScore !== null)
                            <span class="text-sm font-normal text-slate-500">%</span>
                        @endif
                    </p>
                    @if ($scoreDelta !== null)
                        <p class="mt-0.5 flex items-center gap-x-0.5 text-xs font-medium {{ $scoreDelta >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                            <i class="bx {{ $scoreDelta >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                            {{ $scoreDelta >= 0 ? '+' : '' }}{{ $formatScore(abs($scoreDelta)) }}% vs last cycle
                        </p>
                    @endif
                </div>
            </x-card>

            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                    <i class="bx bx-target-lock text-2xl text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Active goals</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $activeGoalsCount }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $onTrackGoalsCount }} on track{{ $goalsAvgProgress !== null ? ' · '.round($goalsAvgProgress).'% avg' : '' }}</p>
                </div>
            </x-card>

            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                    <i class="bx bxs-star text-2xl text-amber-500"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Peer feedback</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">
                        {{ $peerAvgRating ?? '—' }}
                        @if ($peerAvgRating !== null)
                            <span class="text-sm font-normal text-slate-500"> / 5</span>
                        @endif
                    </p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $peerCount }} {{ $peerCount === 1 ? 'review' : 'reviews' }} received</p>
                </div>
            </x-card>

            <x-card class="flex items-center gap-x-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-violet-50">
                    <i class="bx bx-check-circle text-2xl text-violet-600"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Reviews completed</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $completedCount }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $performanceReviews->count() }} total cycles</p>
                </div>
            </x-card>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Main column --}}
            <div class="flex flex-col gap-6 lg:col-span-2">
                {{-- Performance overview --}}
                <x-card>
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                        <x-score-ring :score="$latestScore" :label="$scoreLabel" class="mx-auto sm:mx-0" />
                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-semibold text-slate-900">Performance overview</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                @if ($latestScore === null)
                                    No completed review cycles yet. Your summary will appear once your manager submits your first review.
                                @else
                                    Your latest review scored {{ $formatScore($latestScore) }}% — rated <span class="font-medium text-slate-700">{{ strtolower($scoreLabel) }}</span>.
                                    @if ($scoreDelta !== null)
                                        That's {{ $scoreDelta >= 0 ? 'up' : 'down' }} {{ $formatScore(abs($scoreDelta)) }} points from your previous cycle.
                                    @else
                                        This is your first completed cycle.
                                    @endif
                                @endif
                            </p>
                            <div class="mt-5">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Score trend</p>
                                <x-trend-chart :data="$trend" :height="120" />
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Tabbed sections --}}
                <div>
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex gap-x-6 overflow-x-auto border-b border-slate-200">
                            <button type="button" @click="tab = 'overview'" :class="tab === 'overview' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex shrink-0 items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                                <i class="bx bx-grid-alt text-base"></i> Overview
                            </button>
                            <button type="button" @click="tab = 'reviews'" :class="tab === 'reviews' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex shrink-0 items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                                <i class="bx bx-line-chart text-base"></i> Reviews
                                @if ($pendingSelfReview)
                                    <span class="ml-1 rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white">1</span>
                                @endif
                            </button>
                            <button type="button" @click="tab = 'goals'" :class="tab === 'goals' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex shrink-0 items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                                <i class="bx bx-target-lock text-base"></i> Goals
                            </button>
                            <button type="button" @click="tab = 'feedback'" :class="tab === 'feedback' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex shrink-0 items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                                <i class="bx bx-message-dots text-base"></i> Feedback
                                @if ($pendingFeedbackCount > 0)
                                    <span class="ml-1 rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $pendingFeedbackCount }}</span>
                                @endif
                            </button>
                            @if ($oneOnOnes->isNotEmpty())
                                <button type="button" @click="tab = 'one-on-ones'" :class="tab === 'one-on-ones' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex shrink-0 items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                                    <i class="bx bx-calendar-star text-base"></i> 1-on-1s
                                </button>
                            @endif
                        </div>

                        <div x-show="tab === 'goals'" x-cloak>
                            <x-button icon="bx-plus" type="button" @click="showGoalModal = true">Add goal</x-button>
                        </div>
                    </div>

                    {{-- Overview tab --}}
                    <div x-show="tab === 'overview'">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <x-card class="!p-0 overflow-hidden">
                                <div class="border-b border-slate-100 px-5 py-4">
                                    <h3 class="text-sm font-semibold text-slate-900">Top goals</h3>
                                </div>
                                <div class="divide-y divide-slate-100">
                                    @forelse ($goals->take(3) as $goal)
                                        @php
                                            $progress = $goal->target_value ? min(100, ((float) ($goal->current_value ?? 0) / (float) $goal->target_value) * 100) : null;
                                        @endphp
                                        <div class="px-5 py-4">
                                            <div class="flex items-center justify-between gap-x-2">
                                                <p class="truncate text-sm font-medium text-slate-900">{{ $goal->title }}</p>
                                                <x-badge :color="match($goal->status) { 'completed' => 'success', 'at_risk' => 'warning', 'off_track' => 'danger', default => 'info' }">
                                                    {{ ucfirst(str_replace('_', ' ', $goal->status)) }}
                                                </x-badge>
                                            </div>
                                            @if ($progress !== null)
                                                <div class="mt-2 flex items-center gap-x-2">
                                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                        <div class="h-full rounded-full bg-blue-500" style="width: {{ $progress }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-slate-500">{{ round($progress) }}%</span>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="px-5 py-6 text-center text-sm text-slate-500">No goals yet.</p>
                                    @endforelse
                                </div>
                                @if ($goals->count() > 3)
                                    <div class="border-t border-slate-100 px-5 py-3">
                                        <button type="button" @click="tab = 'goals'" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">View all goals</button>
                                    </div>
                                @endif
                            </x-card>

                            <x-card class="!p-0 overflow-hidden">
                                <div class="border-b border-slate-100 px-5 py-4">
                                    <h3 class="text-sm font-semibold text-slate-900">Recent feedback</h3>
                                </div>
                                <div class="divide-y divide-slate-100">
                                    @forelse ($receivedFeedback->take(3) as $feedback)
                                        <div class="px-5 py-4">
                                            <div class="flex items-start gap-x-3">
                                                <x-avatar :name="$feedback->reviewer->fullName()" size="sm" />
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center justify-between gap-x-2">
                                                        <p class="truncate text-sm font-medium text-slate-900">{{ $feedback->reviewer->fullName() }}</p>
                                                        <div class="flex shrink-0 text-amber-400">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <i class="bx {{ $i <= $feedback->rating ? 'bxs-star' : 'bx-star' }} text-xs"></i>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    @if ($feedback->comments)
                                                        <p class="mt-1 line-clamp-2 text-sm text-slate-600">&ldquo;{{ $feedback->comments }}&rdquo;</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="px-5 py-6 text-center text-sm text-slate-500">No feedback received yet.</p>
                                    @endforelse
                                </div>
                                @if ($receivedFeedback->count() > 3)
                                    <div class="border-t border-slate-100 px-5 py-3">
                                        <button type="button" @click="tab = 'feedback'" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">View all feedback</button>
                                    </div>
                                @endif
                            </x-card>
                        </div>
                    </div>

                    {{-- Reviews tab --}}
                    <div x-show="tab === 'reviews'" x-cloak>
                        <x-card class="!p-0 overflow-hidden">
                            <div class="divide-y divide-slate-100">
                                @forelse ($performanceReviews as $review)
                                    <div class="p-5">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">{{ $review->cycle->name }}</p>
                                                <p class="mt-0.5 text-xs text-slate-400">{{ $review->cycle->start_date->format('M Y') }} – {{ $review->cycle->end_date->format('M Y') }}</p>
                                            </div>
                                            <x-badge :color="$review->status === 'completed' ? 'success' : ($review->status === 'self_submitted' ? 'warning' : 'neutral')">
                                                {{ ucfirst(str_replace('_', ' ', $review->status)) }}
                                            </x-badge>
                                        </div>

                                        @if ($review->status === 'pending')
                                            <form method="POST" action="{{ route('performance.reviews.submit-self', [$review->cycle, $review]) }}" class="mt-4 rounded-lg bg-slate-50 p-4">
                                                @csrf
                                                <p class="mb-3 text-sm font-medium text-slate-700">Submit your self-review</p>
                                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                                                    <div>
                                                        <x-label value="Self rating" />
                                                        <x-select name="rating" class="mt-1">
                                                            @foreach ([1, 2, 3, 4, 5] as $rating)
                                                                <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                                            @endforeach
                                                        </x-select>
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <x-label value="Comments" />
                                                        <x-input name="comments" class="mt-1" placeholder="Reflect on your achievements this cycle" />
                                                    </div>
                                                    <div class="flex items-end">
                                                        <x-button type="submit" class="w-full justify-center">Submit</x-button>
                                                    </div>
                                                </div>
                                            </form>
                                        @else
                                            <div class="mt-4 flex gap-x-8">
                                                <div>
                                                    <p class="text-2xl font-semibold text-slate-900">{{ $review->self_rating ?? '—' }}<span class="text-sm font-normal text-slate-400">{{ $review->self_rating ? '/5' : '' }}</span></p>
                                                    <p class="text-xs text-slate-500">Self rating</p>
                                                </div>
                                                <div>
                                                    <p class="text-2xl font-semibold text-slate-900">{{ $review->manager_rating ?? '—' }}<span class="text-sm font-normal text-slate-400">{{ $review->manager_rating ? '/5' : '' }}</span></p>
                                                    <p class="text-xs text-slate-500">Manager rating</p>
                                                </div>
                                                @if ($review->manager_rating)
                                                    <div>
                                                        <p class="text-2xl font-semibold text-emerald-600">{{ round(((float) $review->manager_rating / 5) * 100) }}<span class="text-sm font-normal text-slate-400">%</span></p>
                                                        <p class="text-xs text-slate-500">Score</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="p-6 text-center text-sm text-slate-500">No performance reviews yet.</p>
                                @endforelse
                            </div>
                        </x-card>
                    </div>

                    {{-- Goals tab --}}
                    <div x-show="tab === 'goals'" x-cloak>
                        <x-card class="!p-0 overflow-hidden">
                            <div class="divide-y divide-slate-100">
                                @forelse ($goals as $goal)
                                    @php
                                        $progress = $goal->target_value ? min(100, ((float) ($goal->current_value ?? 0) / (float) $goal->target_value) * 100) : null;
                                    @endphp
                                    <div class="p-5">
                                        <div class="flex items-center justify-between gap-x-3">
                                            <p class="text-sm font-medium text-slate-900">{{ $goal->title }}</p>
                                            <x-badge :color="match($goal->status) { 'completed' => 'success', 'at_risk' => 'warning', 'off_track' => 'danger', default => 'info' }">
                                                {{ ucfirst(str_replace('_', ' ', $goal->status)) }}
                                            </x-badge>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">
                                            @if ($goal->target_value !== null)
                                                {{ $goal->current_value ?? 0 }} / {{ $goal->target_value }} {{ $goal->unit }}
                                            @endif
                                            @if ($goal->due_date)
                                                &middot; due {{ $goal->due_date->format('d M Y') }}
                                            @endif
                                        </p>
                                        @if ($progress !== null)
                                            <div class="mt-3 flex items-center gap-x-3">
                                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full bg-blue-500" style="width: {{ $progress }}%"></div>
                                                </div>
                                                <span class="text-xs font-medium text-slate-500">{{ round($progress) }}%</span>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-8 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                                            <i class="bx bx-target-lock text-xl text-slate-400"></i>
                                        </div>
                                        <p class="mt-3 text-sm font-medium text-slate-900">No goals yet</p>
                                        <p class="mt-1 text-sm text-slate-500">Set goals to track your progress through the review cycle.</p>
                                        <x-button type="button" class="mt-4" icon="bx-plus" @click="showGoalModal = true">Add your first goal</x-button>
                                    </div>
                                @endforelse
                            </div>
                        </x-card>
                    </div>

                    {{-- Feedback tab --}}
                    <div x-show="tab === 'feedback'" x-cloak>
                        @if ($pendingFeedbackCount > 0)
                            <x-card class="mb-4 !p-0 overflow-hidden">
                                <div class="border-b border-slate-100 px-5 py-4">
                                    <h3 class="text-sm font-semibold text-slate-900">Feedback requested from you</h3>
                                </div>
                                <div class="divide-y divide-slate-100">
                                    @foreach ($pendingFeedbackToGive as $feedback)
                                        <div class="p-5">
                                            <p class="text-sm font-medium text-slate-900">{{ $feedback->review->employee->fullName() }} &middot; {{ $feedback->review->cycle->name }}</p>
                                            <form method="POST" action="{{ route('performance.feedback-requests.submit', $feedback) }}" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4">
                                                @csrf
                                                <x-select name="rating">
                                                    @foreach ([1, 2, 3, 4, 5] as $rating)
                                                        <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                                    @endforeach
                                                </x-select>
                                                <x-input name="comments" placeholder="Your feedback" class="sm:col-span-2" />
                                                <x-button type="submit">Submit</x-button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </x-card>
                        @endif

                        <x-card class="!p-0 overflow-hidden">
                            <div class="border-b border-slate-100 px-5 py-4">
                                <h3 class="text-sm font-semibold text-slate-900">Feedback you've received</h3>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @forelse ($receivedFeedback as $feedback)
                                    <div class="p-5">
                                        <div class="flex items-start gap-x-3">
                                            <x-avatar :name="$feedback->reviewer->fullName()" size="sm" />
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-x-2">
                                                    <p class="text-sm font-medium text-slate-900">{{ $feedback->reviewer->fullName() }}</p>
                                                    <div class="flex shrink-0 text-amber-400">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i class="bx {{ $i <= $feedback->rating ? 'bxs-star' : 'bx-star' }} text-sm"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <p class="text-xs text-slate-400">
                                                    {{ $feedback->reviewer->currentEmployment?->position?->title ?? $feedback->review->cycle->name }}
                                                    &middot; {{ $feedback->submitted_at?->diffForHumans() }}
                                                </p>
                                                @if ($feedback->comments)
                                                    <p class="mt-2 text-sm italic text-slate-600">&ldquo;{{ $feedback->comments }}&rdquo;</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="p-6 text-center text-sm text-slate-500">No peer feedback received yet.</p>
                                @endforelse
                            </div>
                        </x-card>
                    </div>

                    {{-- 1-on-1s tab --}}
                    @if ($oneOnOnes->isNotEmpty())
                        <div x-show="tab === 'one-on-ones'" x-cloak>
                            <x-card class="!p-0 overflow-hidden">
                                <div class="divide-y divide-slate-100">
                                    @foreach ($oneOnOnes as $meeting)
                                        <div class="flex gap-x-4 p-5">
                                            <span @class([
                                                'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                                                'bg-emerald-50' => $meeting->status === 'scheduled',
                                                'bg-slate-100' => $meeting->status !== 'scheduled',
                                            ])>
                                                <i @class([
                                                    'bx bx-calendar-star text-lg',
                                                    'text-emerald-600' => $meeting->status === 'scheduled',
                                                    'text-slate-400' => $meeting->status !== 'scheduled',
                                                ])></i>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-x-2">
                                                    <p class="text-sm font-medium text-slate-900">{{ $meeting->scheduled_at->format('l, d M Y') }}</p>
                                                    <x-badge :color="$meeting->status === 'completed' ? 'success' : ($meeting->status === 'scheduled' ? 'info' : 'neutral')">{{ ucfirst($meeting->status) }}</x-badge>
                                                </div>
                                                <p class="mt-0.5 text-xs text-slate-400">{{ $meeting->scheduled_at->format('H:i') }}</p>
                                                @if ($meeting->agenda)
                                                    <p class="mt-2 text-sm text-slate-600">{{ $meeting->agenda }}</p>
                                                @endif
                                                @if ($meeting->notes)
                                                    <p class="mt-1 text-sm text-slate-500">{{ $meeting->notes }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </x-card>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="flex flex-col gap-6">
                @if ($pendingSelfReview || $pendingFeedbackCount > 0 || $nextOneOnOne)
                    <x-card class="!bg-slate-900 !text-white">
                        @if ($pendingSelfReview)
                            <span class="inline-flex rounded-full bg-amber-400/20 px-2.5 py-1 text-xs font-medium text-amber-300">Action needed</span>
                            <h3 class="mt-3 text-base font-semibold">Self-review due</h3>
                            <p class="mt-2 text-sm text-slate-300">Complete your self-review for <span class="font-medium text-white">{{ $pendingSelfReview->cycle->name }}</span> before your manager can finalize your review.</p>
                            <button type="button" @click="tab = 'reviews'" class="mt-4 block w-full rounded-md bg-white px-4 py-2 text-center text-sm font-semibold text-slate-900 hover:bg-slate-100">Submit self-review</button>
                        @elseif ($pendingFeedbackCount > 0)
                            <span class="inline-flex rounded-full bg-amber-400/20 px-2.5 py-1 text-xs font-medium text-amber-300">Action needed</span>
                            <h3 class="mt-3 text-base font-semibold">{{ $pendingFeedbackCount }} feedback {{ $pendingFeedbackCount === 1 ? 'request' : 'requests' }}</h3>
                            <p class="mt-2 text-sm text-slate-300">Colleagues are waiting for your peer feedback.</p>
                            <button type="button" @click="tab = 'feedback'" class="mt-4 block w-full rounded-md bg-white px-4 py-2 text-center text-sm font-semibold text-slate-900 hover:bg-slate-100">Give feedback</button>
                        @elseif ($nextOneOnOne)
                            <span class="inline-flex rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium">Up next</span>
                            <h3 class="mt-3 text-base font-semibold">Next 1-on-1</h3>
                            <p class="mt-2 text-sm text-slate-300">{{ $nextOneOnOne->scheduled_at->format('D, d M Y \a\t H:i') }}</p>
                            @if ($nextOneOnOne->agenda)
                                <p class="mt-1 text-sm text-slate-400">{{ $nextOneOnOne->agenda }}</p>
                            @endif
                            <button type="button" @click="tab = 'one-on-ones'" class="mt-4 block w-full rounded-md bg-white px-4 py-2 text-center text-sm font-semibold text-slate-900 hover:bg-slate-100">View details</button>
                        @endif
                    </x-card>
                @else
                    <x-card class="!bg-emerald-600 !text-white">
                        <div class="flex items-center gap-x-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/20">
                                <i class="bx bx-check text-xl"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold">All caught up</h3>
                                <p class="mt-0.5 text-sm text-emerald-100">No reviews, feedback, or meetings need your attention.</p>
                            </div>
                        </div>
                    </x-card>
                @endif

                @php
                    $scheduledMeetings = $oneOnOnes->where('status', 'scheduled')->sortBy('scheduled_at');
                @endphp
                @if ($scheduledMeetings->count() > 1 || ($scheduledMeetings->isNotEmpty() && ($pendingSelfReview || $pendingFeedbackCount > 0)))
                    <x-card>
                        <p class="mb-3 text-sm font-semibold text-slate-900">Upcoming 1-on-1s</p>
                        <div class="flex flex-col gap-y-3">
                            @foreach ($scheduledMeetings->take(3) as $meeting)
                                <div class="flex items-center gap-x-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                                        <i class="bx bx-calendar text-sm text-emerald-600"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm text-slate-700">{{ $meeting->scheduled_at->format('D, d M') }}</p>
                                        <p class="text-xs text-slate-400">{{ $meeting->scheduled_at->format('H:i') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                @endif

                <x-card>
                    <p class="mb-4 text-sm font-semibold text-slate-900">Review history</p>
                    @if ($trend !== [])
                        <div class="flex flex-col gap-y-3">
                            @foreach (array_reverse($trend) as $point)
                                <div class="flex items-center justify-between">
                                    <span class="truncate text-sm text-slate-600">{{ $point['label'] }}</span>
                                    <span class="shrink-0 text-sm font-semibold text-slate-900">{{ $formatScore($point['score']) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Complete your first review cycle to see history here.</p>
                    @endif
                </x-card>
            </div>
        </div>

        {{-- Add goal modal --}}
        <div x-show="showGoalModal" x-cloak class="dialog-backdrop fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="showGoalModal = false">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.outside="showGoalModal = false">
                <h2 class="text-base font-semibold text-slate-900">Add a goal</h2>
                <p class="mt-1 text-sm text-slate-500">Track progress toward something you want to achieve this cycle.</p>

                <form method="POST" action="{{ route('performance.goals.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <x-label for="goal_title" value="Goal title" />
                        <x-input id="goal_title" name="title" placeholder="e.g. Complete certification" required class="mt-1" />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="goal_target" value="Target" />
                            <x-input id="goal_target" name="target_value" type="number" step="0.01" placeholder="100" class="mt-1" />
                            <x-input-error :messages="$errors->get('target_value')" class="mt-1" />
                        </div>
                        <div>
                            <x-label for="goal_unit" value="Unit" />
                            <x-input id="goal_unit" name="unit" placeholder="%" class="mt-1" />
                            <x-input-error :messages="$errors->get('unit')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-label for="goal_due_date" value="Due date" />
                        <x-input id="goal_due_date" name="due_date" type="date" class="mt-1" />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-1" />
                    </div>

                    <input type="hidden" name="status" value="on_track">

                    <div class="flex justify-end gap-x-3 pt-2">
                        <x-button type="button" variant="secondary" @click="showGoalModal = false">Cancel</x-button>
                        <x-button type="submit">Add goal</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

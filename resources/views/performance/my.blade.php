@php
    $trend = $performanceReviews
        ->whereNotNull('manager_rating')
        ->sortBy(fn ($review) => $review->cycle->start_date)
        ->map(fn ($review) => ['label' => $review->cycle->name, 'score' => round(((float) $review->manager_rating / 5) * 100, 1)])
        ->values()
        ->all();

    $latestScore = $trend !== [] ? end($trend)['score'] : null;
    $completedCount = $performanceReviews->where('status', 'completed')->count();
    $activeGoals = $goals->whereNotIn('status', ['completed'])->count();
    $nextOneOnOne = $oneOnOnes->where('status', 'scheduled')->sortBy('scheduled_at')->first();
@endphp

<x-layouts.app title="My Performance" header="My Performance">
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-card>
            <p class="text-xs font-medium uppercase text-slate-400">Latest score</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $latestScore !== null ? rtrim(rtrim(number_format($latestScore, 1), '0'), '.').'%' : '—' }}</p>
        </x-card>
        <x-card>
            <p class="text-xs font-medium uppercase text-slate-400">Completed reviews</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $completedCount }}</p>
        </x-card>
        <x-card>
            <p class="text-xs font-medium uppercase text-slate-400">Active goals</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $activeGoals }}</p>
        </x-card>
        <x-card>
            <p class="text-xs font-medium uppercase text-slate-400">Next 1-on-1</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $nextOneOnOne?->scheduled_at->format('d M') ?? '—' }}</p>
        </x-card>
    </div>

    <x-card class="mb-6">
        <h3 class="mb-4 text-sm font-semibold text-slate-900">Score trend</h3>
        <x-trend-chart :data="$trend" />
    </x-card>

    <x-card class="mb-6 !p-0 overflow-hidden">
        <div class="flex items-center gap-x-4 border-b border-slate-100 p-6 pb-5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                <i class="bx bx-line-chart text-xl text-emerald-600"></i>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Performance reviews</h3>
                <p class="mt-1 text-sm text-slate-500">Your review history and any self-review awaiting your input.</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($performanceReviews as $review)
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">{{ $review->cycle->name }}</p>
                        <x-badge :color="$review->status === 'completed' ? 'success' : ($review->status === 'self_submitted' ? 'warning' : 'neutral')">
                            {{ ucfirst(str_replace('_', ' ', $review->status)) }}
                        </x-badge>
                    </div>

                    @if ($review->status === 'pending')
                        <form method="POST" action="{{ route('performance.reviews.submit-self', [$review->cycle, $review]) }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-4">
                            @csrf
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
                                <x-input name="comments" class="mt-1" />
                            </div>
                            <div class="flex items-end">
                                <x-button type="submit">Submit</x-button>
                            </div>
                        </form>
                    @else
                        <div class="mt-3 flex items-center gap-x-6">
                            <div class="text-center">
                                <p class="text-lg font-semibold text-slate-900">{{ $review->self_rating ?? '—' }}<span class="text-xs font-normal text-slate-400">/5</span></p>
                                <p class="text-xs text-slate-500">Self</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-semibold text-slate-900">{{ $review->manager_rating ?? '—' }}<span class="text-xs font-normal text-slate-400">{{ $review->manager_rating ? '/5' : '' }}</span></p>
                                <p class="text-xs text-slate-500">Manager</p>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="p-6 text-center text-sm text-slate-500">No performance reviews yet.</p>
            @endforelse
        </div>
    </x-card>

    @if ($feedbackRequests->where('status', 'pending')->isNotEmpty())
        <x-card class="mb-6 !p-0 overflow-hidden">
            <div class="border-b border-slate-100 p-6 pb-5">
                <h3 class="text-sm font-semibold text-slate-900">Feedback requested from you</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($feedbackRequests->where('status', 'pending') as $feedback)
                    <div class="p-6">
                        <p class="text-sm font-medium text-slate-900">{{ $feedback->review->employee->fullName() }} &middot; {{ $feedback->review->cycle->name }}</p>
                        <form method="POST" action="{{ route('performance.feedback-requests.submit', $feedback) }}" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4">
                            @csrf
                            <x-select name="rating">
                                @foreach ([1, 2, 3, 4, 5] as $rating)
                                    <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                @endforeach
                            </x-select>
                            <x-input name="comments" placeholder="Comments" class="sm:col-span-2" />
                            <x-button type="submit">Submit</x-button>
                        </form>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <x-card class="mb-6 !p-0 overflow-hidden">
        <div class="border-b border-slate-100 p-6 pb-5">
            <h3 class="text-sm font-semibold text-slate-900">Goals</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($goals as $goal)
                @php($progress = $goal->target_value ? min(100, ((float) ($goal->current_value ?? 0) / (float) $goal->target_value) * 100) : null)
                <div class="p-6">
                    <div class="flex items-center justify-between">
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
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $progress }}%"></div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="p-6 text-center text-sm text-slate-500">No goals set yet.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('performance.goals.store') }}" class="grid grid-cols-1 gap-3 border-t border-slate-100 p-6 sm:grid-cols-4">
            @csrf
            <x-input name="title" placeholder="Goal title" required class="sm:col-span-2" />
            <x-input name="target_value" type="number" step="0.01" placeholder="Target" />
            <x-input name="unit" placeholder="Unit (e.g. %)" />
            <x-input name="due_date" type="date" class="sm:col-span-2" />
            <input type="hidden" name="status" value="on_track">
            <x-button type="submit" class="sm:col-span-2">Add goal</x-button>
        </form>
    </x-card>

    @if ($oneOnOnes->isNotEmpty())
        <x-card class="!p-0 overflow-hidden">
            <div class="border-b border-slate-100 p-6 pb-5">
                <h3 class="text-sm font-semibold text-slate-900">1-on-1s</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($oneOnOnes as $meeting)
                    <div class="flex gap-x-4 p-6">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                            <i class="bx bx-calendar-star text-base text-emerald-600"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-slate-900">{{ $meeting->scheduled_at->format('d M Y, H:i') }}</p>
                                <x-badge :color="$meeting->status === 'completed' ? 'success' : 'neutral'">{{ ucfirst($meeting->status) }}</x-badge>
                            </div>
                            @if ($meeting->agenda)
                                <p class="mt-1 text-sm text-slate-500">{{ $meeting->agenda }}</p>
                            @endif
                            @if ($meeting->notes)
                                <p class="mt-1 text-sm text-slate-600">{{ $meeting->notes }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</x-layouts.app>

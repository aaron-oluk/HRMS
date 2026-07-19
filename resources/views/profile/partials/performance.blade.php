<x-card class="!p-0 overflow-hidden">
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
                    <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                        <div>
                            <dt class="text-slate-500">Self rating</dt>
                            <dd class="font-medium text-slate-900">{{ $review->self_rating ?? '—' }} / 5</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Manager rating</dt>
                            <dd class="font-medium text-slate-900">{{ $review->manager_rating ?? '—' }}{{ $review->manager_rating ? ' / 5' : '' }}</dd>
                        </div>
                    </dl>
                @endif
            </div>
        @empty
            <p class="p-6 text-center text-sm text-slate-500">No performance reviews yet.</p>
        @endforelse
    </div>
</x-card>

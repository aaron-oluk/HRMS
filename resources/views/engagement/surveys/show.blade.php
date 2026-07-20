<x-layouts.app title="Survey results" :header="$survey->title">
    <div class="mb-6 flex items-center gap-x-3">
        <x-badge :color="$survey->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($survey->status) }}</x-badge>
        <span class="text-sm text-slate-500">{{ $responseCount }} response(s)</span>
        @if ($survey->is_anonymous)
            <x-badge color="info">Anonymous</x-badge>
        @endif
    </div>

    @if ($survey->status === 'active' && auth()->user()->employee && ! $myResponse)
        <x-card class="mb-6">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Your feedback</h3>
            <form method="POST" action="{{ route('engagement.surveys.respond', $survey) }}" class="space-y-5">
                @csrf
                @foreach ($survey->questions as $question)
                    <div>
                        <input type="hidden" name="answers[{{ $loop->index }}][question_id]" value="{{ $question->id }}">
                        <x-label :value="$question->text" />
                        @if ($question->type === 'rating')
                            <div class="mt-1 flex gap-x-2">
                                @foreach ([1, 2, 3, 4, 5] as $rating)
                                    <label class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-md border border-slate-300 text-sm has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700">
                                        <input type="radio" name="answers[{{ $loop->parent->index }}][rating_value]" value="{{ $rating }}" class="sr-only" required>
                                        {{ $rating }}
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <x-input name="answers[{{ $loop->index }}][text_value]" class="mt-1" />
                        @endif
                    </div>
                @endforeach
                <x-button type="submit">Submit feedback</x-button>
            </form>
        </x-card>
    @elseif ($myResponse)
        <x-alert type="success" class="mb-6">You've already responded to this survey.</x-alert>
    @endif

    @can('engagement.manage')
        <div class="grid grid-cols-1 gap-4">
            @foreach ($results as $result)
                <x-card>
                    <p class="text-sm font-semibold text-slate-900">{{ $result['question']->text }}</p>
                    @if ($result['question']->type === 'rating')
                        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $result['average_rating'] ?? '—' }} <span class="text-sm font-normal text-slate-500">/ 5 average</span></p>
                    @else
                        <ul class="mt-2 space-y-1 text-sm text-slate-600">
                            @forelse ($result['text_answers'] as $answer)
                                <li>&ldquo;{{ $answer }}&rdquo;</li>
                            @empty
                                <li class="text-slate-400">No responses yet.</li>
                            @endforelse
                        </ul>
                    @endif
                </x-card>
            @endforeach
        </div>
    @endcan
</x-layouts.app>

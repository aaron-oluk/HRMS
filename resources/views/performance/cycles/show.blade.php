<x-layouts.app title="Review cycle" :header="$cycle->name">
    <x-card class="mb-6 !p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Employee</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Self rating</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Manager rating</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Peer feedback</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($reviews as $review)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $review->employee->fullName() }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $review->self_rating ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $review->manager_rating ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            @forelse ($review->feedbackRequests as $feedback)
                                <div>{{ $feedback->reviewer->fullName() }}: {{ $feedback->rating ?? 'pending' }}</div>
                            @empty
                                <span class="text-slate-400">None requested</span>
                            @endforelse
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$review->status === 'completed' ? 'success' : ($review->status === 'self_submitted' ? 'warning' : 'neutral')">
                                {{ ucfirst(str_replace('_', ' ', $review->status)) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-x-3">
                                @can('performance.review')
                                    @if ($review->status === 'self_submitted')
                                        <button type="button" @click="$refs['review-{{ $review->id }}'].classList.toggle('hidden')" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">
                                            Review
                                        </button>
                                    @endif
                                    <button type="button" @click="$refs['peer-{{ $review->id }}'].classList.toggle('hidden')" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                                        + Peer
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @can('performance.review')
                        @if ($review->status === 'self_submitted')
                            <tr x-ref="review-{{ $review->id }}" class="hidden">
                                <td colspan="6" class="bg-slate-50 px-4 py-4">
                                    @if ($review->self_comments)
                                        <p class="mb-3 text-sm text-slate-600"><span class="font-medium">Self comments:</span> {{ $review->self_comments }}</p>
                                    @endif
                                    <form method="POST" action="{{ route('performance.reviews.submit-manager', [$cycle, $review]) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                                        @csrf
                                        <x-select name="rating">
                                            @foreach ([1, 2, 3, 4, 5] as $rating)
                                                <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                            @endforeach
                                        </x-select>
                                        <x-input name="comments" placeholder="Manager comments" class="sm:col-span-2" />
                                        <x-button type="submit">Submit review</x-button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                        <tr x-ref="peer-{{ $review->id }}" class="hidden">
                            <td colspan="6" class="bg-slate-50 px-4 py-4">
                                <form method="POST" action="{{ route('performance.feedback-requests.store', $review) }}" class="flex items-center gap-x-3">
                                    @csrf
                                    <x-select name="reviewer_employee_id" class="max-w-xs">
                                        @foreach ($employees as $employee)
                                            @unless ($employee->id === $review->employee_id)
                                                <option value="{{ $employee->id }}">{{ $employee->fullName() }}</option>
                                            @endunless
                                        @endforeach
                                    </x-select>
                                    <x-button type="submit" variant="secondary">Request peer feedback</x-button>
                                </form>
                            </td>
                        </tr>
                    @endcan
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No reviews in this cycle.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    @can('performance.review')
        <x-card>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">1-on-1s</h3>
            </div>

            <form method="POST" action="{{ route('performance.one-on-ones.store') }}" class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-4">
                @csrf
                <x-select name="employee_id">
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->fullName() }}</option>
                    @endforeach
                </x-select>
                <x-input type="datetime-local" name="scheduled_at" required />
                <x-input name="agenda" placeholder="Agenda (optional)" class="sm:col-span-1" />
                <x-button type="submit">Schedule</x-button>
            </form>

            <div class="divide-y divide-slate-100">
                @forelse ($oneOnOnes as $meeting)
                    <div class="py-3" x-data="{ open: false }">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $meeting->employee->fullName() }}</p>
                                <p class="text-xs text-slate-500">{{ $meeting->scheduled_at->format('d M Y, H:i') }} &middot; {{ $meeting->agenda ?? 'No agenda set' }}</p>
                            </div>
                            <div class="flex items-center gap-x-3">
                                <x-badge :color="$meeting->status === 'completed' ? 'success' : 'neutral'">{{ ucfirst($meeting->status) }}</x-badge>
                                @if ($meeting->status !== 'completed')
                                    <button type="button" @click="open = !open" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">Log notes</button>
                                @endif
                            </div>
                        </div>
                        <div x-show="open" x-cloak class="mt-3">
                            <form method="POST" action="{{ route('performance.one-on-ones.notes', $meeting) }}" class="flex items-start gap-x-3">
                                @csrf
                                <textarea name="notes" rows="2" required class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none"></textarea>
                                <x-button type="submit">Save</x-button>
                            </form>
                        </div>
                        @if ($meeting->notes)
                            <p class="mt-2 text-sm text-slate-600">{{ $meeting->notes }}</p>
                        @endif
                    </div>
                @empty
                    <p class="py-3 text-center text-sm text-slate-500">No 1-on-1s scheduled yet.</p>
                @endforelse
            </div>
        </x-card>
    @endcan
</x-layouts.app>

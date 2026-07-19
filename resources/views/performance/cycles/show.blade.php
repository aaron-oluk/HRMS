<x-layouts.app title="Review cycle" :header="$cycle->name">
    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Employee</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Self rating</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Manager rating</th>
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
                        <td class="px-4 py-3">
                            <x-badge :color="$review->status === 'completed' ? 'success' : ($review->status === 'self_submitted' ? 'warning' : 'neutral')">
                                {{ ucfirst(str_replace('_', ' ', $review->status)) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('performance.review')
                                @if ($review->status === 'self_submitted')
                                    <button type="button" @click="$refs['review-{{ $review->id }}'].classList.toggle('hidden')" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">
                                        Review
                                    </button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @can('performance.review')
                        @if ($review->status === 'self_submitted')
                            <tr x-ref="review-{{ $review->id }}" class="hidden">
                                <td colspan="5" class="bg-slate-50 px-4 py-4">
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
                    @endcan
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No reviews in this cycle.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.app>

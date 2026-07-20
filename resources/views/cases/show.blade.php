@php($statusColor = match ($case->status) {
    'open' => 'warning',
    'in_progress' => 'info',
    'resolved', 'closed' => 'success',
    default => 'neutral',
})

<x-layouts.app title="Case" :header="$case->subject">
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-x-3">
            <x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</x-badge>
            <span class="text-sm text-slate-500">{{ ucfirst($case->category) }} &middot; submitted by {{ $case->employee->fullName() }}</span>
        </div>

        @if ($canManage)
            <div class="flex gap-x-3">
                <form method="POST" action="{{ route('cases.assign', $case) }}" class="flex items-center gap-x-2">
                    @csrf
                    <x-select name="assigned_to" onchange="this.form.submit()" class="!py-1.5 !text-xs">
                        <option value="">Assign to&hellip;</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}" @selected($case->assigned_to === $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </x-select>
                </form>
                @if ($case->status !== 'resolved')
                    <form method="POST" action="{{ route('cases.resolve', $case) }}">
                        @csrf
                        <x-button type="submit" variant="secondary">Mark resolved</x-button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <x-card class="mb-6">
        <p class="text-sm text-slate-600">{{ $case->description }}</p>
    </x-card>

    <x-card class="mb-6 !p-0 overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse ($comments as $comment)
                <div class="p-4 {{ $comment->is_internal ? 'bg-amber-50' : '' }}">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">{{ $comment->author->name }}</p>
                        <div class="flex items-center gap-x-2">
                            @if ($comment->is_internal)
                                <x-badge color="warning">Internal note</x-badge>
                            @endif
                            <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">{{ $comment->body }}</p>
                </div>
            @empty
                <p class="p-4 text-center text-sm text-slate-500">No replies yet.</p>
            @endforelse
        </div>
    </x-card>

    <x-card>
        <form method="POST" action="{{ route('cases.comment', $case) }}" class="space-y-3">
            @csrf
            <textarea name="body" rows="3" required placeholder="Write a reply&hellip;" class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none"></textarea>
            <div class="flex items-center justify-between">
                @if ($canManage)
                    <label class="flex items-center gap-x-2 text-sm text-slate-600">
                        <x-checkbox name="is_internal" value="1" />
                        Internal note (not visible to the employee)
                    </label>
                @else
                    <span></span>
                @endif
                <x-button type="submit">Reply</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

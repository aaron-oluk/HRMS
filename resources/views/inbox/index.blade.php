<x-layouts.app title="Inbox" header="Inbox">
    <p class="mb-6 text-sm text-slate-500">Everything waiting on your approval, oldest first.</p>

    @if ($items->isEmpty())
        <x-card>
            <p class="text-sm text-slate-500">You're all caught up — nothing needs your attention right now.</p>
        </x-card>
    @else
        <div class="flex flex-col gap-4">
            @foreach ($items as $item)
                <x-card class="flex items-center justify-between gap-x-4">
                    <div class="flex items-center gap-x-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                            <i class="bx {{ $item['icon'] }} text-xl text-emerald-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $item['employee'] }} · {{ $item['type'] }}</p>
                            <p class="text-sm text-slate-500">{{ $item['summary'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">Submitted {{ $item['submitted_at']->diffForHumans() }}</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-x-2">
                        @if (isset($item['approve_route']))
                            <form method="POST" action="{{ $item['approve_route'] }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-x-1 rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                    <i class="bx bx-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ $item['reject_route'] }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-x-1 rounded-md bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                    <i class="bx bx-x"></i> Deny
                                </button>
                            </form>
                        @else
                            <a href="{{ $item['action_route'] }}" class="inline-flex items-center gap-x-1 rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                {{ $item['action_label'] }}
                            </a>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</x-layouts.app>

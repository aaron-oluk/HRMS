@php($report ??= [])

<x-card class="relative flex h-full flex-col transition hover:shadow-md">
    <a href="{{ route($report['route']) }}" class="absolute inset-0 z-0" aria-label="{{ $report['title'] }}"></a>

    <div class="relative z-10 flex items-start justify-between">
        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
            <i class="bx {{ $report['icon'] }} text-xl text-emerald-600"></i>
        </span>

        <form method="POST" action="{{ $report['favorited'] ? route('reports.favorites.destroy', $report['key']) : route('reports.favorites.store') }}" class="relative z-20">
            @csrf
            @if ($report['favorited'])
                @method('DELETE')
            @else
                <input type="hidden" name="report_key" value="{{ $report['key'] }}">
            @endif
            <button type="submit" class="text-lg {{ $report['favorited'] ? 'text-amber-500' : 'text-slate-300 hover:text-amber-400' }}" aria-label="{{ $report['favorited'] ? 'Remove from favorites' : 'Add to favorites' }}">
                <i class="bx {{ $report['favorited'] ? 'bxs-star' : 'bx-star' }}"></i>
            </button>
        </form>
    </div>

    <h3 class="relative z-10 mt-4 text-base font-semibold text-slate-900">{{ $report['title'] }}</h3>
    <p class="relative z-10 mt-1.5 text-sm text-slate-500">{{ $report['description'] }}</p>

    <div class="relative z-10 mt-4">
        <x-bar-chart :data="$report['trend']" compact />
    </div>

    <div class="relative z-10 mt-3 flex items-center justify-between">
        <x-badge color="neutral">{{ $report['category'] }}</x-badge>
        <span class="flex items-center gap-x-1 text-xs text-slate-400">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Live
        </span>
    </div>
</x-card>

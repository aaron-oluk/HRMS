<x-layouts.app title="Reports" header="Reports">
    @if ($favorites->isNotEmpty())
        <div class="mb-8">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Your favorites</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($favorites as $report)
                    @include('reports.partials.tile', ['report' => $report])
                @endforeach
            </div>
        </div>
    @endif

    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">
        {{ $favorites->isNotEmpty() ? 'All reports' : 'Recommended for you' }}
    </h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reports as $report)
            @include('reports.partials.tile', ['report' => $report])
        @endforeach
    </div>
</x-layouts.app>

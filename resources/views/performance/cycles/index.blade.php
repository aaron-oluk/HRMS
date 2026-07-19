<x-layouts.app title="Performance" header="Performance review cycles">
    <div class="mb-4 flex justify-end">
        @can('performance.manage-cycles')
            <a href="{{ route('performance.cycles.create') }}"><x-button icon="bx-plus">New cycle</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Cycle</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Period</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Reviews</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($cycles as $cycle)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $cycle->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $cycle->start_date->format('d M Y') }} &ndash; {{ $cycle->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $cycle->reviews_count }}</td>
                        <td class="px-4 py-3"><x-badge :color="$cycle->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($cycle->status) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('performance.cycles.show', $cycle) }}" class="text-emerald-600 hover:text-emerald-500">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No review cycles yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $cycles->links() }}</div>
</x-layouts.app>

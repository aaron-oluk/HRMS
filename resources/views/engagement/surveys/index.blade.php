<x-layouts.app title="Engagement" header="Engagement surveys">
    <div class="mb-4 flex justify-end">
        @can('engagement.manage')
            <a href="{{ route('engagement.surveys.create') }}"><x-button icon="bx-plus">Launch survey</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Title</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Responses</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($surveys as $survey)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $survey->title }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $survey->responses_count }}</td>
                        <td class="px-4 py-3"><x-badge :color="$survey->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($survey->status) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('engagement.surveys.show', $survey) }}" class="text-emerald-600 hover:text-emerald-500">View results</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No surveys yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $surveys->links() }}</div>
</x-layouts.app>

<x-layouts.app title="Documents" header="Documents">
    <div class="mb-4 flex justify-end">
        @can('esignature.send')
            <a href="{{ route('documents.create') }}"><x-button icon="bx-plus">Send for signature</x-button></a>
        @endcan
    </div>

    <x-card class="!p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Title</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Signer</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($documents as $document)
                    @php($statusColor = match ($document->status) {
                        'sent' => 'warning',
                        'signed' => 'success',
                        'declined' => 'danger',
                        default => 'neutral',
                    })
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $document->title }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $document->signer->name }}</td>
                        <td class="px-4 py-3"><x-badge :color="$statusColor">{{ ucfirst($document->status) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('documents.show', $document) }}" class="text-emerald-600 hover:text-emerald-500">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No documents yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $documents->links() }}</div>
</x-layouts.app>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <x-card class="!p-0 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">File</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Uploaded</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employee->documents as $document)
                        <tr>
                            <td class="px-4 py-3 text-slate-900">{{ $document->original_filename }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ ucfirst(str_replace('_', ' ', $document->type)) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $document->created_at->toDateString() }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('employees.manage-documents')
                                    <form method="POST" action="{{ route('employees.documents.destroy', [$employee, $document]) }}" onsubmit="return confirm('Delete this document?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No documents uploaded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>

    @can('employees.manage-documents')
        <x-card>
            <h3 class="text-sm font-semibold text-slate-900">Upload document</h3>
            <form method="POST" action="{{ route('employees.documents.store', $employee) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <x-label for="doc_type" value="Type" />
                    <x-select id="doc_type" name="type" class="mt-1">
                        @foreach (['contract', 'national_id', 'certificate', 'other'] as $type)
                            <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <x-label for="file" value="File" />
                    <input id="file" type="file" name="file" required class="mt-1 block w-full text-sm text-slate-500">
                    <x-input-error :messages="$errors->get('file')" class="mt-1" />
                </div>
                <x-button type="submit" icon="bx-upload" class="w-full">Upload</x-button>
            </form>
        </x-card>
    @endcan
</div>

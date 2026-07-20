<x-layouts.app title="My Signature" header="My Signature">
    <x-card class="max-w-xl">
        <div class="mb-6 flex items-center gap-x-4 border-b border-slate-100 pb-5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                <i class="bx bx-pen text-xl text-emerald-600"></i>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">My signature</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Upload a photo or scan of your signature on plain paper. The background is removed
                    automatically so it can be dropped onto documents.
                </p>
            </div>
        </div>

        @if (auth()->user()->signature_path)
            <div class="mb-4 rounded-md border border-dashed border-slate-300 bg-[repeating-conic-gradient(#f8fafc_0%_25%,white_0%_50%)] bg-[length:16px_16px] p-4">
                <img src="{{ route('profile.signature.show') }}?v={{ auth()->user()->updated_at->timestamp }}" alt="Your signature" class="h-20">
            </div>
        @endif

        <form method="POST" action="{{ route('profile.signature.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="file" name="signature" accept="image/png,image/jpeg" required class="block w-full text-sm text-slate-500">
            <x-input-error :messages="$errors->get('signature')" />
            <x-button type="submit" icon="bx-upload">{{ auth()->user()->signature_path ? 'Replace signature' : 'Upload signature' }}</x-button>
        </form>
    </x-card>
</x-layouts.app>

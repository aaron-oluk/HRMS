<x-layouts.app title="Send for signature" header="Send for signature">
    <x-card class="max-w-xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Send a document for signature</h2>
            <p class="mt-1 text-sm text-slate-500">PDF only. The signer will be notified and can sign it from their inbox.</p>
        </div>

        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <x-label for="title" value="Title" />
                    <x-input id="title" name="title" :value="old('title')" required autofocus class="mt-1" placeholder="e.g. Offer letter — Jane Doe" />
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>

                <div>
                    <x-label for="signer_user_id" value="Signer" />
                    <x-select id="signer_user_id" name="signer_user_id" class="mt-1">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('signer_user_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('signer_user_id')" class="mt-1" />
                </div>

                <div>
                    <x-label for="file" value="Document (PDF)" />
                    <input id="file" type="file" name="file" accept="application/pdf" required class="mt-1 block w-full text-sm text-slate-500">
                    <x-input-error :messages="$errors->get('file')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('documents.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Send</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

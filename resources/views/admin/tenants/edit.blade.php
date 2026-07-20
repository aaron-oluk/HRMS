<x-layouts.admin title="Edit company" header="Edit company">
    <x-card class="max-w-2xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Edit company</h2>
            <p class="mt-1 text-sm text-slate-500">Updates the company's own details. Does not touch its users.</p>
        </div>

        <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-label for="name" value="Company name" />
                    <x-input id="name" name="name" :value="old('name', $tenant->name)" required autofocus class="mt-1" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-label for="timezone" value="Timezone" />
                    <x-input id="timezone" name="timezone" :value="old('timezone', $tenant->timezone)" required class="mt-1" />
                    <x-input-error :messages="$errors->get('timezone')" class="mt-1" />
                </div>

                <div>
                    <x-label for="currency" value="Currency" />
                    <x-input id="currency" name="currency" :value="old('currency', $tenant->currency)" required maxlength="3" class="mt-1" />
                    <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                </div>
            </div>

            <div class="flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.tenants.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save changes</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.admin>

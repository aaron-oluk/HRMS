<x-layouts.admin title="Add platform admin" header="Add platform admin">
    <x-card class="max-w-2xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Add platform admin</h2>
            <p class="mt-1 text-sm text-slate-500">Platform admins can manage every company on Aloflux and bypass tenant-level permissions. Only add people who need that level of access.</p>
        </div>

        <form method="POST" action="{{ route('admin.super-admins.store') }}" class="space-y-5">
            @csrf

            <div>
                <x-label for="name" value="Name" />
                <x-input id="name" name="name" :value="old('name')" required autofocus class="mt-1" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div>
                <x-label for="email" value="Email" />
                <x-input id="email" type="email" name="email" :value="old('email')" required class="mt-1" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div>
                <x-label for="password" value="Password" />
                <x-password-input id="password" name="password" required class="mt-1" />
                <p class="mt-1 text-xs text-slate-500">Minimum 8 characters. Share this with them directly.</p>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.super-admins.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Add platform admin</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.admin>

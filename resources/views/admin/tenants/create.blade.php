<x-layouts.admin title="Onboard a company" header="Onboard a company">
    <x-card class="max-w-2xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Onboard a company</h2>
            <p class="mt-1 text-sm text-slate-500">Creates the company's account, provisions its default roles, and creates its first HR Admin user.</p>
        </div>

        <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-8">
            @csrf

            <div>
                <h3 class="text-sm font-semibold text-slate-900">Company</h3>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-label for="name" value="Company name" />
                        <x-input id="name" name="name" :value="old('name')" required autofocus class="mt-1" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-label for="timezone" value="Timezone" />
                        <x-input id="timezone" name="timezone" :value="old('timezone', 'Africa/Kampala')" required class="mt-1" />
                        <x-input-error :messages="$errors->get('timezone')" class="mt-1" />
                    </div>

                    <div>
                        <x-label for="currency" value="Currency" />
                        <x-input id="currency" name="currency" :value="old('currency', 'UGX')" required maxlength="3" class="mt-1" />
                        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h3 class="text-sm font-semibold text-slate-900">First HR Admin</h3>
                <p class="mt-0.5 text-sm text-slate-500">This person can sign in immediately and will invite the rest of their team.</p>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-label for="admin_name" value="Name" />
                        <x-input id="admin_name" name="admin_name" :value="old('admin_name')" required class="mt-1" />
                        <x-input-error :messages="$errors->get('admin_name')" class="mt-1" />
                    </div>

                    <div>
                        <x-label for="admin_email" value="Email" />
                        <x-input id="admin_email" type="email" name="admin_email" :value="old('admin_email')" required class="mt-1" />
                        <x-input-error :messages="$errors->get('admin_email')" class="mt-1" />
                    </div>

                    <div>
                        <x-label for="admin_password" value="Password" />
                        <x-password-input id="admin_password" name="admin_password" required class="mt-1" />
                        <p class="mt-1 text-xs text-slate-500">Minimum 8 characters. Share this with them directly.</p>
                        <x-input-error :messages="$errors->get('admin_password')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.tenants.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Onboard company</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.admin>

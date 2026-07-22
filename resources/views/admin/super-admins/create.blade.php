<x-layouts.admin title="Add platform admin" header="Add platform admin">
    <x-card class="max-w-2xl" x-data="{ tier: '{{ old('tier', 'global') }}' }">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Add platform admin</h2>
            <p class="mt-1 text-sm text-slate-500">Global admins can manage every company on Aloflux. Org Admins are scoped to specific companies only. Both bypass tenant-level permissions within their scope.</p>
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

            <div>
                <x-label value="Access level" />
                <div class="mt-2 space-y-3">
                    <label class="flex cursor-pointer items-start gap-x-3 rounded-md border border-slate-200 p-4 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50">
                        <input type="radio" name="tier" value="global" x-model="tier" class="mt-1">
                        <span>
                            <span class="block text-sm font-medium text-slate-900">Global</span>
                            <span class="block text-sm text-slate-500">Full access to every company, unrestricted.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-x-3 rounded-md border border-slate-200 p-4 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50">
                        <input type="radio" name="tier" value="org" x-model="tier" class="mt-1">
                        <span>
                            <span class="block text-sm font-medium text-slate-900">Org Admin</span>
                            <span class="block text-sm text-slate-500">Scoped to specific companies only.</span>
                        </span>
                    </label>
                </div>
                <x-input-error :messages="$errors->get('tier')" class="mt-1" />
            </div>

            <div x-show="tier === 'org'" x-cloak>
                <x-label value="Assigned companies" />
                <div class="mt-2 grid grid-cols-1 gap-2 rounded-md border border-slate-200 p-4 sm:grid-cols-2">
                    @forelse ($tenants as $tenant)
                        <label class="flex items-center gap-x-2 text-sm text-slate-700">
                            <x-checkbox name="tenant_ids[]" value="{{ $tenant->id }}" @checked(collect(old('tenant_ids'))->contains($tenant->id)) />
                            {{ $tenant->name }}
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">No companies onboarded yet.</p>
                    @endforelse
                </div>
                <x-input-error :messages="$errors->get('tenant_ids')" class="mt-1" />
            </div>

            <div class="flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.super-admins.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Add platform admin</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.admin>

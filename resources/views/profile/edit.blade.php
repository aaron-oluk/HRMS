<x-layouts.app title="My Profile" header="My Profile">
    <div x-data="{ tab: 'profile' }" class="max-w-3xl">
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex gap-x-6">
                <button type="button" @click="tab = 'profile'" :class="tab === 'profile' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-user text-base"></i> Profile
                </button>
                <button type="button" @click="tab = 'password'" :class="tab === 'password' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-lock-alt text-base"></i> Password
                </button>
                <button type="button" @click="tab = 'security'" :class="tab === 'security' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-shield-quarter text-base"></i> Two-Factor Authentication
                </button>
            </nav>
        </div>

        <div class="mt-6" x-show="tab === 'profile'">
            @include('profile.partials.profile-information')
        </div>

        <div class="mt-6" x-show="tab === 'password'" x-cloak>
            @include('profile.partials.update-password')
        </div>

        <div class="mt-6" x-show="tab === 'security'" x-cloak>
            @include('profile.partials.two-factor')
        </div>
    </div>
</x-layouts.app>

<x-layouts.guest title="Two-factor authentication">
    <h2 class="text-lg font-semibold text-slate-900">Two-factor authentication</h2>
    <p class="mt-1 text-sm text-slate-500" x-data="{ recovery: false }">
        <span x-show="!recovery">Enter the 6-digit code from your authenticator app.</span>
        <span x-show="recovery" x-cloak>Enter one of your recovery codes.</span>
    </p>

    <form method="POST" action="{{ route('two-factor.login') }}" class="mt-6 space-y-5" x-data="{ recovery: false }">
        @csrf

        <div x-show="!recovery">
            <x-label for="code" value="Authentication code" />
            <x-input id="code" type="text" inputmode="numeric" name="code" autofocus autocomplete="one-time-code" class="mt-1" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div x-show="recovery" x-cloak>
            <x-label for="recovery_code" value="Recovery code" />
            <x-input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code" class="mt-1" />
            <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <button type="button" @click="recovery = !recovery" class="text-sm text-emerald-600 hover:text-emerald-500">
                <span x-show="!recovery">Use a recovery code</span>
                <span x-show="recovery" x-cloak>Use an authentication code</span>
            </button>
        </div>

        <x-button type="submit" class="w-full">Log in</x-button>
    </form>
</x-layouts.guest>

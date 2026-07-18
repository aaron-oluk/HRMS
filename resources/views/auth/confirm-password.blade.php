<x-layouts.guest title="Confirm password">
    <h2 class="text-lg font-semibold text-slate-900">Confirm your password</h2>
    <p class="mt-1 text-sm text-slate-500">This is a secure area. Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-label for="password" value="Password" />
            <x-password-input id="password" name="password" required autofocus autocomplete="current-password" class="mt-1" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-button type="submit" class="w-full">Confirm</x-button>
    </form>
</x-layouts.guest>

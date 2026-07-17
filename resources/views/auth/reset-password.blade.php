<x-layouts.guest title="Reset password">
    <h2 class="text-lg font-semibold text-slate-900">Reset your password</h2>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-label for="email" value="Email" />
            <x-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus class="mt-1" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-label for="password" value="New password" />
            <x-input id="password" type="password" name="password" required autocomplete="new-password" class="mt-1" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-label for="password_confirmation" value="Confirm new password" />
            <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-button type="submit" class="w-full">Reset password</x-button>
    </form>
</x-layouts.guest>

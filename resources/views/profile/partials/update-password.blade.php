<x-card class="max-w-xl">
    <h3 class="text-sm font-semibold text-slate-900">Update password</h3>
    <p class="mt-1 text-sm text-slate-500">Use a long, random password to keep your account secure.</p>

    @if (session('status') === 'password-updated')
        <x-alert type="success" class="mt-4">Password updated.</x-alert>
    @endif

    <form method="POST" action="{{ route('user-password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <x-label for="current_password" value="Current password" />
            <x-input id="current_password" type="password" name="current_password" required autocomplete="current-password" class="mt-1" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <div>
            <x-label for="password" value="New password" />
            <x-input id="password" type="password" name="password" required autocomplete="new-password" class="mt-1" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
        </div>

        <div>
            <x-label for="password_confirmation" value="Confirm new password" />
            <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>

        <x-button type="submit">Update password</x-button>
    </form>
</x-card>

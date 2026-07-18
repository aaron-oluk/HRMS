<x-card class="max-w-xl">
    <div class="mb-6 flex items-center gap-x-4 border-b border-slate-100 pb-5">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
            <i class="bx bx-lock-alt text-xl text-emerald-600"></i>
        </span>
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Update password</h3>
            <p class="mt-1 text-sm text-slate-500">Use a long, random password to keep your account secure.</p>
        </div>
    </div>

    @if (session('status') === 'password-updated')
        <x-alert type="success" class="mb-6">Password updated.</x-alert>
    @endif

    <form method="POST" action="{{ route('user-password.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <x-label for="current_password" value="Current password" />
            <x-password-input id="current_password" name="current_password" required autocomplete="current-password" class="mt-1" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <div>
            <x-label for="password" value="New password" />
            <x-password-input id="password" name="password" required autocomplete="new-password" class="mt-1" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
        </div>

        <div>
            <x-label for="password_confirmation" value="Confirm new password" />
            <x-password-input id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="mt-1" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>

        <x-button type="submit">Update password</x-button>
    </form>
</x-card>

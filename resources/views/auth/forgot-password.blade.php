<x-layouts.guest title="Forgot password">
    <h2 class="text-lg font-semibold text-slate-900">Forgot your password?</h2>
    <p class="mt-1 text-sm text-slate-500">Enter your email and we'll send you a password reset link.</p>

    @if (session('status'))
        <x-alert type="success" class="mt-4">{{ session('status') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-label for="email" value="Email" />
            <x-input id="email" type="email" name="email" :value="old('email')" required autofocus class="mt-1" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-button type="submit" class="w-full">Email password reset link</x-button>
    </form>
</x-layouts.guest>

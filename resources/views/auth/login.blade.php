<x-layouts.guest title="Log in">
    <h2 class="text-xl font-semibold text-slate-900">Welcome back</h2>
    <p class="mt-1 text-sm text-slate-500">Log in to your Aloflux HRMS account.</p>

    @if (session('status'))
        <x-alert type="success" class="mt-4">{{ session('status') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-label for="email" value="Email" />
            <x-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" class="mt-1" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-label for="password" value="Password" />
            <x-input id="password" type="password" name="password" required autocomplete="current-password" class="mt-1" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-x-2 text-sm text-slate-600">
                <x-checkbox name="remember" />
                Remember me
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-emerald-600 hover:text-emerald-500">
                    Forgot your password?
                </a>
            @endif
        </div>

        <x-button type="submit" class="w-full">Log in</x-button>
    </form>
</x-layouts.guest>

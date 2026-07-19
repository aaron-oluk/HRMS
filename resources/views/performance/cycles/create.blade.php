<x-layouts.app title="New review cycle" header="New review cycle">
    <x-card class="max-w-xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">New review cycle</h2>
            <p class="mt-1 text-sm text-slate-500">Opens a self + manager review for every active employee.</p>
        </div>

        <form method="POST" action="{{ route('performance.cycles.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <x-label for="name" value="Name" />
                    <x-input id="name" name="name" :value="old('name')" required autofocus class="mt-1" placeholder="e.g. 2026 H2" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-label for="start_date" value="Start date" />
                    <x-input id="start_date" type="date" name="start_date" :value="old('start_date')" required class="mt-1" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                </div>

                <div>
                    <x-label for="end_date" value="End date" />
                    <x-input id="end_date" type="date" name="end_date" :value="old('end_date')" required class="mt-1" />
                    <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('performance.cycles.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Create</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

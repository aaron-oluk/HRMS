<x-layouts.app title="Add leave type" header="Add leave type">
    <x-card class="max-w-3xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Add leave type</h2>
            <p class="mt-1 text-sm text-slate-500">Define a new type of leave for your organization.</p>
        </div>

        <form method="POST" action="{{ route('leave-types.store') }}">
            @csrf
            @include('leave-types._form')

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('leave-types.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

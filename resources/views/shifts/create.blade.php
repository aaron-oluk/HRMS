<x-layouts.app title="Add shift" header="Add shift">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('shifts.store') }}">
            @csrf
            @include('shifts._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('shifts.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

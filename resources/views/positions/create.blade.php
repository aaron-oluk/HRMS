<x-layouts.app title="Add position" header="Add position">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('positions.store') }}">
            @csrf
            @include('positions._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('positions.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

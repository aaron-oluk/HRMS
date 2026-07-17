<x-layouts.app title="Add entity" header="Add entity">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('entities.store') }}">
            @csrf
            @include('entities._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('entities.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

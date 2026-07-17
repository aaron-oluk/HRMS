<x-layouts.app title="Edit department" header="Edit department">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('departments.update', $department) }}">
            @csrf
            @method('PUT')
            @include('departments._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('departments.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

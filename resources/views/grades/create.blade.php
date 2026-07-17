<x-layouts.app title="Add grade" header="Add grade">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('grades.store') }}">
            @csrf
            @include('grades._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('grades.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

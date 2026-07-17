<x-layouts.app title="Edit employee" header="Edit employee">
    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf
            @method('PUT')
            @include('employees._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('employees.show', $employee) }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

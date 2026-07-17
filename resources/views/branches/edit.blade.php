<x-layouts.app title="Edit branch" header="Edit branch">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('branches.update', $branch) }}">
            @csrf
            @method('PUT')
            @include('branches._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('branches.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

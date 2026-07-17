<x-layouts.app title="Edit user" header="Edit user">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('users._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('users.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

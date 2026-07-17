<x-layouts.app title="Edit leave type" header="Edit leave type">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('leave-types.update', $leaveType) }}">
            @csrf
            @method('PUT')
            @include('leave-types._form')

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('leave-types.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

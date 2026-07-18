<x-layouts.app title="Edit department" header="Edit department">
    <x-card class="max-w-3xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Edit department</h2>
            <p class="mt-1 text-sm text-slate-500">Update this department's details.</p>
        </div>

        <form method="POST" action="{{ route('departments.update', $department) }}">
            @csrf
            @method('PUT')
            @include('departments._form')

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('departments.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

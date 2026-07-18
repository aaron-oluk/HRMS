<x-layouts.app title="Edit employee" header="Edit employee">
    <x-card class="max-w-4xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Edit employee</h2>
            <p class="mt-1 text-sm text-slate-500">Update this employee's profile details.</p>
        </div>

        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf
            @method('PUT')
            @include('employees._form')

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('employees.show', $employee) }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

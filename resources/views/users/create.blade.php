<x-layouts.app title="Add user" header="Add user">
    <x-card class="max-w-2xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Add user</h2>
            <p class="mt-1 text-sm text-slate-500">Invite a new user and assign their role.</p>
        </div>

        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('users._form')

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('users.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

<x-layouts.admin title="Add theme" header="Add theme">
    <x-card class="max-w-3xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Add theme</h2>
            <p class="mt-1 text-sm text-slate-500">Available to every company as a preset — no per-tenant customization beyond picking one.</p>
        </div>

        <form method="POST" action="{{ route('admin.themes.store') }}">
            @csrf
            @include('admin.themes._form')

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.themes.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.admin>

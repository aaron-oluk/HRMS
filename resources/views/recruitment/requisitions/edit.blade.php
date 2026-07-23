<x-layouts.app title="Edit role" header="Edit role">
    <x-card class="max-w-3xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Edit role</h2>
        </div>

        <form method="POST" action="{{ route('recruitment.requisitions.update', $jobRequisition) }}">
            @csrf
            @method('PUT')
            @include('recruitment.requisitions._form')

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('recruitment.requisitions.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

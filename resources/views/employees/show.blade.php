<x-layouts.app :title="$employee->fullName()" :header="$employee->fullName()">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $employee->employee_number }} · {{ $employee->entity->name }}</p>
        @can('employees.manage')
            <a href="{{ route('employees.edit', $employee) }}"><x-button variant="secondary">Edit profile</x-button></a>
        @endcan
    </div>

    <div x-data="{ tab: 'profile' }">
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex gap-x-6">
                <button type="button" @click="tab = 'profile'" :class="tab === 'profile' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-1 py-3 text-sm font-medium">Profile</button>
                <button type="button" @click="tab = 'employment'" :class="tab === 'employment' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-1 py-3 text-sm font-medium">Employment history</button>
                <button type="button" @click="tab = 'documents'" :class="tab === 'documents' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-1 py-3 text-sm font-medium">Documents</button>
                <button type="button" @click="tab = 'pay'" :class="tab === 'pay' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-1 py-3 text-sm font-medium">Bank &amp; mobile money</button>
            </nav>
        </div>

        <div class="mt-6" x-show="tab === 'profile'">
            @include('employees.partials.profile')
        </div>

        <div class="mt-6" x-show="tab === 'employment'" x-cloak>
            @include('employees.partials.employment-history')
        </div>

        <div class="mt-6" x-show="tab === 'documents'" x-cloak>
            @include('employees.partials.documents')
        </div>

        <div class="mt-6" x-show="tab === 'pay'" x-cloak>
            @include('employees.partials.pay-accounts')
        </div>
    </div>
</x-layouts.app>

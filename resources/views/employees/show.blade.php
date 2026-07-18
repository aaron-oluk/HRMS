<x-layouts.app :title="$employee->fullName()" :header="$employee->fullName()">
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-x-3">
            <x-avatar :name="$employee->fullName()" size="lg" />
            <div>
                <p class="text-base font-semibold text-slate-900">{{ $employee->fullName() }}</p>
                <p class="flex items-center gap-x-1.5 text-sm text-slate-500">
                    <i class="bx bx-id-card text-base"></i>
                    {{ $employee->employee_number }} · {{ $employee->entity->name }}
                </p>
            </div>
        </div>
        @can('employees.update')
            <a href="{{ route('employees.edit', $employee) }}"><x-button variant="secondary" icon="bx-edit-alt">Edit profile</x-button></a>
        @endcan
    </div>

    <div x-data="{ tab: 'profile' }">
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex gap-x-6">
                <button type="button" @click="tab = 'profile'" :class="tab === 'profile' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-user text-base"></i> Profile
                </button>
                <button type="button" @click="tab = 'employment'" :class="tab === 'employment' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-briefcase text-base"></i> Employment history
                </button>
                <button type="button" @click="tab = 'documents'" :class="tab === 'documents' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-file text-base"></i> Documents
                </button>
                <button type="button" @click="tab = 'pay'" :class="tab === 'pay' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-wallet text-base"></i> Bank &amp; mobile money
                </button>
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

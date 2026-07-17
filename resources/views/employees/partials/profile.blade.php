<x-card>
    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-medium uppercase text-slate-500">Gender</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $employee->gender ? ucfirst($employee->gender) : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase text-slate-500">Marital status</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $employee->marital_status ? ucfirst($employee->marital_status) : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase text-slate-500">Phone</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $employee->phone ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase text-slate-500">Personal email</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $employee->personal_email ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase text-slate-500">Nationality</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $employee->nationality ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase text-slate-500">Status</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ ucfirst(str_replace('_', ' ', $employee->status)) }}</dd>
        </div>

        @can('employees.view-sensitive')
            <div>
                <dt class="text-xs font-medium uppercase text-slate-500">Date of birth</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $employee->date_of_birth?->toFormattedDateString() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-500">National ID</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $employee->national_id_number ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-500">NSSF number</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $employee->nssf_number ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-500">TIN</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $employee->tin_number ?? '—' }}</dd>
            </div>
        @endcan
    </dl>
</x-card>

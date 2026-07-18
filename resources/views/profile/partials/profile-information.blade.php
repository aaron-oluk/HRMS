@php
    $employee = auth()->user()->employee;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <x-card class="lg:col-span-2">
        <h3 class="text-sm font-semibold text-slate-900">Your details</h3>
        <p class="mt-1 text-sm text-slate-500">Update your name and email address.</p>

        @if (session('status') === 'profile-information-updated')
            <x-alert type="success" class="mt-4">Profile updated.</x-alert>
        @endif

        <form method="POST" action="{{ route('user-profile-information.update') }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <x-label for="name" value="Name" />
                <x-input id="name" name="name" :value="old('name', auth()->user()->name)" required autofocus autocomplete="name" class="mt-1" />
                <x-input-error :messages="$errors->updateProfileInformation->get('name')" class="mt-1" />
            </div>

            <div>
                <x-label for="email" value="Email" />
                <x-input id="email" type="email" name="email" :value="old('email', auth()->user()->email)" required autocomplete="username" class="mt-1" />
                <x-input-error :messages="$errors->updateProfileInformation->get('email')" class="mt-1" />
            </div>

            <x-button type="submit">Save changes</x-button>
        </form>
    </x-card>

    <x-card>
        <h3 class="text-sm font-semibold text-slate-900">Account</h3>
        <dl class="mt-4 space-y-4 text-sm">
            <div>
                <dt class="text-xs font-medium uppercase text-slate-500">Role</dt>
                <dd class="mt-1 text-slate-900">{{ auth()->user()->getRoleNames()->first() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-500">Status</dt>
                <dd class="mt-1">
                    @php
                        $statusColor = match (auth()->user()->status) {
                            'active' => 'success',
                            'invited' => 'info',
                            'suspended' => 'danger',
                            default => 'neutral',
                        };
                    @endphp
                    <x-badge :color="$statusColor">{{ ucfirst(auth()->user()->status) }}</x-badge>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-slate-500">Organization</dt>
                <dd class="mt-1 text-slate-900">{{ auth()->user()->tenant?->name ?? '—' }}</dd>
            </div>

            @if ($employee)
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Employee #</dt>
                    <dd class="mt-1 text-slate-900">{{ $employee->employee_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Position</dt>
                    <dd class="mt-1 text-slate-900">{{ $employee->currentEmployment?->position?->title ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Department</dt>
                    <dd class="mt-1 text-slate-900">{{ $employee->currentEmployment?->department?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Entity</dt>
                    <dd class="mt-1 text-slate-900">{{ $employee->entity?->name ?? '—' }}</dd>
                </div>
            @endif
        </dl>
    </x-card>
</div>

@php
    $employee = auth()->user()->employee;

    $accountRows = collect([
        ['icon' => 'bx-id-card', 'label' => 'Role', 'value' => auth()->user()->getRoleNames()->first() ?? '—'],
        ['icon' => 'bx-buildings', 'label' => 'Organization', 'value' => auth()->user()->tenant?->name ?? '—'],
    ]);

    if ($employee) {
        $accountRows->push(['icon' => 'bx-hash', 'label' => 'Employee #', 'value' => $employee->employee_number]);
        $accountRows->push(['icon' => 'bx-briefcase', 'label' => 'Position', 'value' => $employee->currentEmployment?->position?->title ?? '—']);
        $accountRows->push(['icon' => 'bx-sitemap', 'label' => 'Department', 'value' => $employee->currentEmployment?->department?->name ?? '—']);
        $accountRows->push(['icon' => 'bx-building-house', 'label' => 'Entity', 'value' => $employee->entity?->name ?? '—']);
    }

    $statusColor = match (auth()->user()->status) {
        'active' => 'success',
        'invited' => 'info',
        'suspended' => 'danger',
        default => 'neutral',
    };
@endphp

<div class="grid grid-cols-2 gap-6">
    <x-card>
        <div class="mb-6 flex items-center gap-x-4 border-b border-slate-100 pb-5">
            <x-avatar :name="auth()->user()->name" size="lg" />
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Your details</h3>
                <p class="mt-1 text-sm text-slate-500">Update your name and email address.</p>
            </div>
        </div>

        @if (session('status') === 'profile-information-updated')
            <x-alert type="success" class="mb-6">Profile updated.</x-alert>
        @endif

        <form method="POST" action="{{ route('user-profile-information.update') }}" class="space-y-5">
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
        <div class="mb-2 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-sm font-semibold text-slate-900">Account</h3>
            <x-badge :color="$statusColor">{{ ucfirst(auth()->user()->status) }}</x-badge>
        </div>
        <dl class="divide-y divide-slate-100">
            @foreach ($accountRows as $row)
                <div class="flex items-center justify-between gap-x-3 py-3">
                    <dt class="flex items-center gap-x-2 text-sm text-slate-500">
                        <i class="bx {{ $row['icon'] }} text-base text-slate-400"></i>
                        {{ $row['label'] }}
                    </dt>
                    <dd class="truncate text-sm font-medium text-slate-900">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </x-card>
</div>

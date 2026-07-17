@php($user ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" :value="old('name', $user?->name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-label for="email" value="Email" />
        <x-input id="email" type="email" name="email" :value="old('email', $user?->email)" required class="mt-1" />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div>
        <x-label for="password" value="Password" />
        <x-input id="password" type="password" name="password" autocomplete="new-password" class="mt-1" />
        <p class="mt-1 text-xs text-slate-500">{{ $user ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.' }}</p>
        <x-input-error :messages="$errors->get('password')" class="mt-1" />
    </div>

    <div>
        <x-label for="employee_id" value="Linked employee" />
        <x-select id="employee_id" name="employee_id" class="mt-1">
            <option value="">None</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $user?->employee_id) == $employee->id)>{{ $employee->fullName() }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('employee_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="role" value="Role" />
        <x-select id="role" name="role" class="mt-1">
            @foreach (['HR Admin', 'Manager', 'Employee'] as $role)
                <option value="{{ $role }}" @selected(old('role', $user?->getRoleNames()->first()) === $role)>{{ $role }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('role')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'invited', 'suspended'] as $status)
                <option value="{{ $status }}" @selected(old('status', $user?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

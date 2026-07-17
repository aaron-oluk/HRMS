@php($employee ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <x-label for="entity_id" value="Entity" />
        <x-select id="entity_id" name="entity_id" class="mt-1">
            @foreach ($entities as $entity)
                <option value="{{ $entity->id }}" @selected(old('entity_id', $employee?->entity_id) == $entity->id)>{{ $entity->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="employee_number" value="Employee number" />
        <x-input id="employee_number" name="employee_number" :value="old('employee_number', $employee?->employee_number)" required class="mt-1" />
        <x-input-error :messages="$errors->get('employee_number')" class="mt-1" />
    </div>

    <div>
        <x-label for="first_name" value="First name" />
        <x-input id="first_name" name="first_name" :value="old('first_name', $employee?->first_name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
    </div>

    <div>
        <x-label for="last_name" value="Last name" />
        <x-input id="last_name" name="last_name" :value="old('last_name', $employee?->last_name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
    </div>

    <div>
        <x-label for="other_names" value="Other names" />
        <x-input id="other_names" name="other_names" :value="old('other_names', $employee?->other_names)" class="mt-1" />
        <x-input-error :messages="$errors->get('other_names')" class="mt-1" />
    </div>

    <div>
        <x-label for="gender" value="Gender" />
        <x-select id="gender" name="gender" class="mt-1">
            <option value="">Select</option>
            @foreach (['male', 'female', 'other'] as $gender)
                <option value="{{ $gender }}" @selected(old('gender', $employee?->gender) === $gender)>{{ ucfirst($gender) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('gender')" class="mt-1" />
    </div>

    <div>
        <x-label for="date_of_birth" value="Date of birth" />
        <x-input id="date_of_birth" type="date" name="date_of_birth" :value="old('date_of_birth', $employee?->date_of_birth?->toDateString())" class="mt-1" />
        <x-input-error :messages="$errors->get('date_of_birth')" class="mt-1" />
    </div>

    <div>
        <x-label for="national_id_number" value="National ID number" />
        <x-input id="national_id_number" name="national_id_number" :value="old('national_id_number', $employee?->national_id_number)" class="mt-1" />
        <x-input-error :messages="$errors->get('national_id_number')" class="mt-1" />
    </div>

    <div>
        <x-label for="nssf_number" value="NSSF number" />
        <x-input id="nssf_number" name="nssf_number" :value="old('nssf_number', $employee?->nssf_number)" class="mt-1" />
        <x-input-error :messages="$errors->get('nssf_number')" class="mt-1" />
    </div>

    <div>
        <x-label for="tin_number" value="TIN" />
        <x-input id="tin_number" name="tin_number" :value="old('tin_number', $employee?->tin_number)" class="mt-1" />
        <x-input-error :messages="$errors->get('tin_number')" class="mt-1" />
    </div>

    <div>
        <x-label for="phone" value="Phone" />
        <x-input id="phone" name="phone" :value="old('phone', $employee?->phone)" class="mt-1" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
    </div>

    <div>
        <x-label for="personal_email" value="Personal email" />
        <x-input id="personal_email" type="email" name="personal_email" :value="old('personal_email', $employee?->personal_email)" class="mt-1" />
        <x-input-error :messages="$errors->get('personal_email')" class="mt-1" />
    </div>

    <div>
        <x-label for="marital_status" value="Marital status" />
        <x-select id="marital_status" name="marital_status" class="mt-1">
            <option value="">Select</option>
            @foreach (['single', 'married', 'divorced', 'widowed'] as $status)
                <option value="{{ $status }}" @selected(old('marital_status', $employee?->marital_status) === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('marital_status')" class="mt-1" />
    </div>

    <div>
        <x-label for="nationality" value="Nationality" />
        <x-input id="nationality" name="nationality" :value="old('nationality', $employee?->nationality ?? 'Ugandan')" class="mt-1" />
        <x-input-error :messages="$errors->get('nationality')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'on_leave', 'suspended', 'exited'] as $status)
                <option value="{{ $status }}" @selected(old('status', $employee?->status ?? 'active') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

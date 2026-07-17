@php($entity ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" :value="old('name', $entity?->name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-label for="registration_number" value="Registration number" />
        <x-input id="registration_number" name="registration_number" :value="old('registration_number', $entity?->registration_number)" class="mt-1" />
        <x-input-error :messages="$errors->get('registration_number')" class="mt-1" />
    </div>

    <div>
        <x-label for="tax_identification_number" value="Tax identification number (TIN)" />
        <x-input id="tax_identification_number" name="tax_identification_number" :value="old('tax_identification_number', $entity?->tax_identification_number)" class="mt-1" />
        <x-input-error :messages="$errors->get('tax_identification_number')" class="mt-1" />
    </div>

    <div>
        <x-label for="nssf_employer_number" value="NSSF employer number" />
        <x-input id="nssf_employer_number" name="nssf_employer_number" :value="old('nssf_employer_number', $entity?->nssf_employer_number)" class="mt-1" />
        <x-input-error :messages="$errors->get('nssf_employer_number')" class="mt-1" />
    </div>

    <div>
        <x-label for="currency" value="Currency" />
        <x-input id="currency" name="currency" :value="old('currency', $entity?->currency ?? 'UGX')" required maxlength="3" class="mt-1" />
        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
    </div>

    <div class="sm:col-span-2">
        <x-label for="address" value="Address" />
        <x-input id="address" name="address" :value="old('address', $entity?->address)" class="mt-1" />
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $entity?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

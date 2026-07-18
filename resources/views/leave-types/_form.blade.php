@php($leaveType ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-label for="entity_id" value="Entity" />
        <x-select id="entity_id" name="entity_id" class="mt-1">
            @foreach ($entities as $entity)
                <option value="{{ $entity->id }}" @selected(old('entity_id', $leaveType?->entity_id) == $entity->id)>{{ $entity->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" :value="old('name', $leaveType?->name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-label for="code" value="Code" />
        <x-input id="code" name="code" :value="old('code', $leaveType?->code)" class="mt-1" />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div>
        <x-label for="default_days_per_year" value="Days per year" />
        <x-input id="default_days_per_year" type="number" step="0.5" name="default_days_per_year" :value="old('default_days_per_year', $leaveType?->default_days_per_year ?? 21)" required class="mt-1" />
        <x-input-error :messages="$errors->get('default_days_per_year')" class="mt-1" />
    </div>

    <div>
        <x-label for="max_carry_forward_days" value="Max carry-forward days" />
        <x-input id="max_carry_forward_days" type="number" step="0.5" name="max_carry_forward_days" :value="old('max_carry_forward_days', $leaveType?->max_carry_forward_days)" class="mt-1" />
        <x-input-error :messages="$errors->get('max_carry_forward_days')" class="mt-1" />
    </div>

    <div class="flex items-center gap-x-2 pt-6">
        <input type="hidden" name="is_paid" value="0">
        <x-checkbox id="is_paid" name="is_paid" value="1" @checked(old('is_paid', $leaveType?->is_paid ?? true)) />
        <x-label for="is_paid" value="Paid leave" class="!mb-0" />
    </div>

    <div class="flex items-center gap-x-2 pt-6">
        <input type="hidden" name="requires_approval" value="0">
        <x-checkbox id="requires_approval" name="requires_approval" value="1" @checked(old('requires_approval', $leaveType?->requires_approval ?? true)) />
        <x-label for="requires_approval" value="Requires approval" class="!mb-0" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $leaveType?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

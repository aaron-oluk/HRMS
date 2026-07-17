@php($grade ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-label for="entity_id" value="Entity" />
        <x-select id="entity_id" name="entity_id" class="mt-1">
            @foreach ($entities as $entity)
                <option value="{{ $entity->id }}" @selected(old('entity_id', $grade?->entity_id) == $entity->id)>{{ $entity->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" :value="old('name', $grade?->name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-label for="code" value="Code" />
        <x-input id="code" name="code" :value="old('code', $grade?->code)" class="mt-1" />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div>
        <x-label for="level" value="Level" />
        <x-input id="level" type="number" name="level" :value="old('level', $grade?->level ?? 1)" required class="mt-1" />
        <x-input-error :messages="$errors->get('level')" class="mt-1" />
    </div>

    <div>
        <x-label for="currency" value="Currency" />
        <x-input id="currency" name="currency" :value="old('currency', $grade?->currency ?? 'UGX')" required maxlength="3" class="mt-1" />
        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
    </div>

    <div>
        <x-label for="min_salary" value="Minimum salary" />
        <x-input id="min_salary" type="number" step="0.01" name="min_salary" :value="old('min_salary', $grade?->min_salary)" class="mt-1" />
        <x-input-error :messages="$errors->get('min_salary')" class="mt-1" />
    </div>

    <div>
        <x-label for="max_salary" value="Maximum salary" />
        <x-input id="max_salary" type="number" step="0.01" name="max_salary" :value="old('max_salary', $grade?->max_salary)" class="mt-1" />
        <x-input-error :messages="$errors->get('max_salary')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $grade?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

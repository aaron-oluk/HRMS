@php($position ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <x-label for="entity_id" value="Entity" />
        <x-select id="entity_id" name="entity_id" class="mt-1">
            @foreach ($entities as $entity)
                <option value="{{ $entity->id }}" @selected(old('entity_id', $position?->entity_id) == $entity->id)>{{ $entity->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="department_id" value="Department" />
        <x-select id="department_id" name="department_id" class="mt-1">
            <option value="">None</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $position?->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('department_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="title" value="Title" />
        <x-input id="title" name="title" :value="old('title', $position?->title)" required class="mt-1" />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div>
        <x-label for="code" value="Code" />
        <x-input id="code" name="code" :value="old('code', $position?->code)" class="mt-1" />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $position?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

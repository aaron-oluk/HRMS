@php($department ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <x-label for="entity_id" value="Entity" />
        <x-select id="entity_id" name="entity_id" class="mt-1">
            @foreach ($entities as $entity)
                <option value="{{ $entity->id }}" @selected(old('entity_id', $department?->entity_id) == $entity->id)>{{ $entity->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="parent_department_id" value="Parent department" />
        <x-select id="parent_department_id" name="parent_department_id" class="mt-1">
            <option value="">None</option>
            @foreach ($departments as $option)
                <option value="{{ $option->id }}" @selected(old('parent_department_id', $department?->parent_department_id) == $option->id)>{{ $option->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('parent_department_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" :value="old('name', $department?->name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-label for="code" value="Code" />
        <x-input id="code" name="code" :value="old('code', $department?->code)" class="mt-1" />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $department?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

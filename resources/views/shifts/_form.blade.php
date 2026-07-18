@php($shift ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-label for="entity_id" value="Entity" />
        <x-select id="entity_id" name="entity_id" class="mt-1">
            @foreach ($entities as $entity)
                <option value="{{ $entity->id }}" @selected(old('entity_id', $shift?->entity_id) == $entity->id)>{{ $entity->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
    </div>

    <div class="sm:col-span-2">
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" :value="old('name', $shift?->name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-label for="start_time" value="Start time" />
        <x-input id="start_time" type="time" name="start_time" :value="old('start_time', $shift?->formattedStartTime())" required class="mt-1" />
        <x-input-error :messages="$errors->get('start_time')" class="mt-1" />
    </div>

    <div>
        <x-label for="end_time" value="End time" />
        <x-input id="end_time" type="time" name="end_time" :value="old('end_time', $shift?->formattedEndTime())" required class="mt-1" />
        <x-input-error :messages="$errors->get('end_time')" class="mt-1" />
    </div>

    <div>
        <x-label for="break_minutes" value="Break (minutes)" />
        <x-input id="break_minutes" type="number" name="break_minutes" :value="old('break_minutes', $shift?->break_minutes ?? 60)" required class="mt-1" />
        <x-input-error :messages="$errors->get('break_minutes')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $shift?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

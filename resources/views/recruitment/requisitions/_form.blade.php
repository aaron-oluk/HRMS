@php($jobRequisition ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-label for="title" value="Title" />
        <x-input id="title" name="title" :value="old('title', $jobRequisition?->title)" required class="mt-1" />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div>
        <x-label for="entity_id" value="Entity" />
        <x-select id="entity_id" name="entity_id" class="mt-1">
            @foreach ($entities as $entity)
                <option value="{{ $entity->id }}" @selected(old('entity_id', $jobRequisition?->entity_id) == $entity->id)>{{ $entity->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="department_id" value="Department" />
        <x-select id="department_id" name="department_id" class="mt-1">
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $jobRequisition?->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('department_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="position_id" value="Position" />
        <x-select id="position_id" name="position_id" class="mt-1">
            @foreach ($positions as $position)
                <option value="{{ $position->id }}" @selected(old('position_id', $jobRequisition?->position_id) == $position->id)>{{ $position->title }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('position_id')" class="mt-1" />
    </div>

    <div>
        <x-label for="headcount" value="Headcount" />
        <x-input id="headcount" type="number" min="1" name="headcount" :value="old('headcount', $jobRequisition?->headcount ?? 1)" required class="mt-1" />
        <x-input-error :messages="$errors->get('headcount')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['draft', 'open', 'on_hold', 'closed', 'filled'] as $status)
                <option value="{{ $status }}" @selected(old('status', $jobRequisition?->status ?? 'draft') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>

    <div class="sm:col-span-2">
        <x-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none">{{ old('description', $jobRequisition?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>
</div>

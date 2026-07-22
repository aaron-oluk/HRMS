@php($branch ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-label for="entity_id" value="Entity" />
        <x-select id="entity_id" name="entity_id" class="mt-1">
            @foreach ($entities as $entity)
                <option value="{{ $entity->id }}" @selected(old('entity_id', $branch?->entity_id) == $entity->id)>{{ $entity->name }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
    </div>

    @if (auth()->user()->tenant?->isSegmented())
        <div class="sm:col-span-2">
            <x-label for="area_id" value="Area" />
            <x-select id="area_id" name="area_id" class="mt-1">
                <option value="">— None —</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected(old('area_id', $branch?->area_id) == $area->id)>{{ $area->name }}</option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('area_id')" class="mt-1" />
        </div>
    @endif

    <div>
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" :value="old('name', $branch?->name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-label for="code" value="Code" />
        <x-input id="code" name="code" :value="old('code', $branch?->code)" class="mt-1" />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div class="sm:col-span-2">
        <x-label for="address" value="Address" />
        <x-input id="address" name="address" :value="old('address', $branch?->address)" class="mt-1" />
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div>
        <x-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1">
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $branch?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>
</div>

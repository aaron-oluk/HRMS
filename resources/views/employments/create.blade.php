<x-layouts.app title="Record employment change" :header="'Record employment change · '.$employee->fullName()">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('employees.employments.store', $employee) }}">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-label for="entity_id" value="Entity" />
                    <x-select id="entity_id" name="entity_id" class="mt-1">
                        @foreach ($entities as $entity)
                            <option value="{{ $entity->id }}" @selected(old('entity_id') == $entity->id)>{{ $entity->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('entity_id')" class="mt-1" />
                </div>

                <div>
                    <x-label for="department_id" value="Department" />
                    <x-select id="department_id" name="department_id" class="mt-1">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('department_id')" class="mt-1" />
                </div>

                <div>
                    <x-label for="position_id" value="Position" />
                    <x-select id="position_id" name="position_id" class="mt-1">
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->title }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('position_id')" class="mt-1" />
                </div>

                <div>
                    <x-label for="grade_id" value="Grade" />
                    <x-select id="grade_id" name="grade_id" class="mt-1">
                        <option value="">None</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" @selected(old('grade_id') == $grade->id)>{{ $grade->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('grade_id')" class="mt-1" />
                </div>

                <div>
                    <x-label for="employment_type" value="Employment type" />
                    <x-select id="employment_type" name="employment_type" class="mt-1">
                        @foreach (['full_time', 'part_time', 'contract', 'intern'] as $type)
                            <option value="{{ $type }}" @selected(old('employment_type', 'full_time') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('employment_type')" class="mt-1" />
                </div>

                <div>
                    <x-label for="reason" value="Reason for change" />
                    <x-select id="reason" name="reason" class="mt-1">
                        @foreach (['initial', 'promotion', 'transfer', 'salary_review', 'probation', 'demotion', 'other'] as $reason)
                            <option value="{{ $reason }}" @selected(old('reason') === $reason)>{{ ucfirst(str_replace('_', ' ', $reason)) }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                </div>

                <div>
                    <x-label for="basic_salary" value="Basic salary" />
                    <x-input id="basic_salary" type="number" step="0.01" name="basic_salary" :value="old('basic_salary')" required class="mt-1" />
                    <x-input-error :messages="$errors->get('basic_salary')" class="mt-1" />
                </div>

                <div>
                    <x-label for="currency" value="Currency" />
                    <x-input id="currency" name="currency" :value="old('currency', 'UGX')" required maxlength="3" class="mt-1" />
                    <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                </div>

                <div>
                    <x-label for="effective_from" value="Effective from" />
                    <x-input id="effective_from" type="date" name="effective_from" :value="old('effective_from', now()->toDateString())" required class="mt-1" />
                    <x-input-error :messages="$errors->get('effective_from')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-x-3">
                <a href="{{ route('employees.show', $employee) }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Save</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

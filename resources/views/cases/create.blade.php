<x-layouts.app title="New case" header="New case">
    <x-card class="max-w-2xl">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">New case</h2>
            <p class="mt-1 text-sm text-slate-500">HR will follow up privately — only you and HR can see this.</p>
        </div>

        <form method="POST" action="{{ route('cases.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <x-label for="category" value="Category" />
                    <x-select id="category" name="category" class="mt-1">
                        @foreach (\App\Models\HrCase::CATEGORIES as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ ucfirst($category) }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>

                <div>
                    <x-label for="subject" value="Subject" />
                    <x-input id="subject" name="subject" :value="old('subject')" required autofocus class="mt-1" />
                    <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                </div>

                <div>
                    <x-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="5" required class="mt-1 block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('cases.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Submit</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

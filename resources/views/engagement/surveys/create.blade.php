<x-layouts.app title="Launch survey" header="Launch survey">
    <x-card class="max-w-3xl" x-data="{
        questions: [{ text: '', type: 'rating' }],
        addQuestion() { this.questions.push({ text: '', type: 'rating' }); },
        removeQuestion(index) { this.questions.splice(index, 1); },
    }">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Launch survey</h2>
            <p class="mt-1 text-sm text-slate-500">Sent to every active employee. They can each respond once.</p>
        </div>

        <form method="POST" action="{{ route('engagement.surveys.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <x-label for="title" value="Title" />
                    <x-input id="title" name="title" :value="old('title')" required autofocus class="mt-1" placeholder="e.g. Q3 pulse check" />
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>

                <div>
                    <x-label for="description" value="Description" />
                    <x-input id="description" name="description" :value="old('description')" class="mt-1" />
                </div>

                <label class="flex items-center gap-x-2 text-sm text-slate-600">
                    <x-checkbox name="is_anonymous" value="1" />
                    Keep individual responses anonymous in the results view
                </label>

                <div>
                    <x-label for="closes_at" value="Closes at (optional)" />
                    <x-input id="closes_at" type="date" name="closes_at" :value="old('closes_at')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-5">
                <h3 class="text-sm font-semibold text-slate-900">Questions</h3>

                <template x-for="(question, index) in questions" :key="index">
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-6">
                        <div class="sm:col-span-4">
                            <input type="text" :name="`questions[${index}][text]`" x-model="question.text" required
                                class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none"
                                placeholder="Question text">
                        </div>
                        <div class="sm:col-span-1">
                            <select :name="`questions[${index}][type]`" x-model="question.type"
                                class="block w-full appearance-none rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none">
                                <option value="rating">Rating (1-5)</option>
                                <option value="text">Free text</option>
                            </select>
                        </div>
                        <div class="flex items-center sm:col-span-1">
                            <button type="button" @click="removeQuestion(index)" x-show="questions.length > 1" class="text-sm text-red-600 hover:text-red-500">Remove</button>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addQuestion" class="mt-4 text-sm font-medium text-emerald-600 hover:text-emerald-500">
                    + Add another question
                </button>
                <x-input-error :messages="$errors->get('questions')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('engagement.surveys.index') }}"><x-button type="button" variant="secondary">Cancel</x-button></a>
                <x-button type="submit">Launch</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

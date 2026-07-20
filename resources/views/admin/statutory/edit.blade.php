<x-layouts.admin title="Statutory configuration" header="Statutory configuration">
    <x-card class="max-w-3xl" x-data="{
        bands: {{ Illuminate\Support\Js::from($bands->map(fn ($band) => ['floor' => (string) $band->floor, 'rate' => (string) $band->rate])) }},
        addBand() { this.bands.push({ floor: '', rate: '' }); },
        removeBand(index) { this.bands.splice(index, 1); },
    }">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Statutory configuration</h2>
            <p class="mt-1 text-sm text-slate-500">Uganda PAYE bands and NSSF rates used by every payroll run, across every company. Changes apply to runs generated after saving.</p>
        </div>

        <form method="POST" action="{{ route('admin.statutory.update') }}">
            @csrf
            @method('PUT')

            <div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">PAYE bands</h3>
                </div>
                <p class="mt-0.5 text-sm text-slate-500">Each band taxes only the slice of monthly income above its floor and below the next band's floor. The first band should have a floor of 0.</p>

                <template x-for="(band, index) in bands" :key="index">
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <input type="number" step="0.01" min="0" :name="`bands[${index}][floor]`" x-model="band.floor" required
                                class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none"
                                placeholder="Floor (UGX)">
                        </div>
                        <div class="sm:col-span-2">
                            <input type="number" step="0.0001" min="0" max="1" :name="`bands[${index}][rate]`" x-model="band.rate" required
                                class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none"
                                placeholder="Rate (e.g. 0.10)">
                        </div>
                        <div class="flex items-center sm:col-span-1">
                            <button type="button" @click="removeBand(index)" x-show="bands.length > 1" class="text-sm text-red-600 hover:text-red-500">Remove</button>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addBand" class="mt-4 text-sm font-medium text-emerald-600 hover:text-emerald-500">
                    + Add another band
                </button>
                <x-input-error :messages="$errors->get('bands')" class="mt-2" />
            </div>

            <div class="mt-6 border-t border-slate-100 pt-6">
                <h3 class="text-sm font-semibold text-slate-900">PAYE surcharge</h3>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-label for="paye_surcharge_floor" value="Surcharge floor (UGX)" />
                        <x-input id="paye_surcharge_floor" type="number" step="0.01" min="0" name="paye_surcharge_floor" :value="old('paye_surcharge_floor', $settings->paye_surcharge_floor)" required class="mt-1" />
                        <x-input-error :messages="$errors->get('paye_surcharge_floor')" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="paye_surcharge_rate" value="Surcharge rate" />
                        <x-input id="paye_surcharge_rate" type="number" step="0.0001" min="0" max="1" name="paye_surcharge_rate" :value="old('paye_surcharge_rate', $settings->paye_surcharge_rate)" required class="mt-1" />
                        <x-input-error :messages="$errors->get('paye_surcharge_rate')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-6">
                <h3 class="text-sm font-semibold text-slate-900">NSSF</h3>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-label for="nssf_employee_rate" value="Employee rate" />
                        <x-input id="nssf_employee_rate" type="number" step="0.0001" min="0" max="1" name="nssf_employee_rate" :value="old('nssf_employee_rate', $settings->nssf_employee_rate)" required class="mt-1" />
                        <x-input-error :messages="$errors->get('nssf_employee_rate')" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="nssf_employer_rate" value="Employer rate" />
                        <x-input id="nssf_employer_rate" type="number" step="0.0001" min="0" max="1" name="nssf_employer_rate" :value="old('nssf_employer_rate', $settings->nssf_employer_rate)" required class="mt-1" />
                        <x-input-error :messages="$errors->get('nssf_employer_rate')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-x-3 border-t border-slate-100 pt-5">
                <x-button type="submit">Save changes</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.admin>

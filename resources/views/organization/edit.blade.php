<x-layouts.app title="Organization Settings" header="Organization Settings">
    <div x-data="{ tab: 'general' }">
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex gap-x-6">
                <button type="button" @click="tab = 'general'" :class="tab === 'general' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-buildings text-base"></i> General
                </button>
                <button type="button" @click="tab = 'statutory'" :class="tab === 'statutory' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-calculator text-base"></i> Statutory
                </button>
                <button type="button" @click="tab = 'theme'" :class="tab === 'theme' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-palette text-base"></i> Theme
                </button>
                <button type="button" @click="tab = 'structure'" :class="tab === 'structure' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-sitemap text-base"></i> Structure
                </button>
            </nav>
        </div>

        <div class="mt-6" x-show="tab === 'general'">
            @include('organization.partials.general')
        </div>

        <div class="mt-6" x-show="tab === 'statutory'" x-cloak>
            @include('organization.partials.statutory')
        </div>

        <div class="mt-6" x-show="tab === 'theme'" x-cloak>
            @include('organization.partials.theme')
        </div>

        <div class="mt-6" x-show="tab === 'structure'" x-cloak>
            @include('organization.partials.structure')
        </div>
    </div>
</x-layouts.app>

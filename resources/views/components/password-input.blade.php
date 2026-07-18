@props(['disabled' => false])

<div x-data="{ show: false }" class="relative">
    <input
        @disabled($disabled)
        :type="show ? 'text' : 'password'"
        {{ $attributes->merge(['class' => 'block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 pr-10 text-sm text-slate-900 transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500 disabled:hover:border-slate-300']) }}
    >
    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <button
            type="button"
            tabindex="-1"
            class="pointer-events-auto flex items-center text-slate-400 hover:text-slate-600"
            @click="show = !show"
            :aria-label="show ? 'Hide password' : 'Show password'"
        >
            <i class="bx text-base leading-none" :class="show ? 'bx-hide' : 'bx-show'"></i>
        </button>
    </span>
</div>
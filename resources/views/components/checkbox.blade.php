@props(['disabled' => false])

<input
    type="checkbox"
    @disabled($disabled)
    {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-slate-300 text-emerald-600 shadow-sm transition focus:ring-2 focus:ring-emerald-500/30 focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50']) }}
>

@props(['disabled' => false])

<input
    type="checkbox"
    @disabled($disabled)
    {{ $attributes->merge(['class' => 'h-4 w-4 rounded-sm border-slate-300 text-emerald-600 transition disabled:cursor-not-allowed disabled:opacity-50']) }}
>

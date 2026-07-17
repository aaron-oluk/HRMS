@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-md border-slate-300 shadow-sm text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-50 disabled:text-slate-500']) }}>
    {{ $slot }}
</select>

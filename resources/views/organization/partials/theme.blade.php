@php($currentThemeId = $tenant->theme_id)

<x-card class="max-w-3xl">
    <div class="mb-6 border-b border-slate-100 pb-5">
        <h2 class="text-base font-semibold text-slate-900">Theme</h2>
        <p class="mt-1 text-sm text-slate-500">Pick a color and font preset for your company. Changes apply the next time a page loads.</p>
    </div>

    <form method="POST" action="{{ route('organization.update-theme') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($themes as $theme)
                <label class="flex cursor-pointer flex-col gap-y-3 rounded-md border border-slate-200 p-4 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-900">{{ $theme->name }}</span>
                        <input type="radio" name="theme_id" value="{{ $theme->id }}" @checked((int) $currentThemeId === $theme->id)>
                    </div>
                    <div class="flex gap-x-1.5">
                        @foreach (['color_50', 'color_100', 'color_500', 'color_600', 'color_700', 'color_800'] as $shade)
                            <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $theme->$shade }}"></span>
                        @endforeach
                    </div>
                    <p class="truncate text-xs text-slate-500" style="font-family: {{ $theme->font_stack }}">{{ $theme->font_stack }}</p>
                </label>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">
            <x-button type="submit">Save theme</x-button>
        </div>
    </form>
</x-card>

@php($theme ??= null)

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" :value="old('name', $theme?->name)" required class="mt-1" />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-label for="slug" value="Slug" />
        <x-input id="slug" name="slug" :value="old('slug', $theme?->slug)" required class="mt-1" />
        <x-input-error :messages="$errors->get('slug')" class="mt-1" />
    </div>

    <div class="sm:col-span-2">
        <x-label value="Colors" />
        <p class="mt-0.5 text-xs text-slate-500">Only these 6 shades are used anywhere in the app.</p>
        <div class="mt-2 grid grid-cols-3 gap-3 sm:grid-cols-6">
            @foreach (['50', '100', '500', '600', '700', '800'] as $shade)
                <div>
                    <label for="color_{{ $shade }}" class="block text-center text-xs text-slate-500">{{ $shade }}</label>
                    <input
                        type="color" id="color_{{ $shade }}" name="color_{{ $shade }}"
                        value="{{ old('color_'.$shade, $theme?->{'color_'.$shade} ?? '#000000') }}"
                        class="mt-1 h-10 w-full cursor-pointer rounded-sm border border-slate-300"
                    >
                    <x-input-error :messages="$errors->get('color_'.$shade)" class="mt-1" />
                </div>
            @endforeach
        </div>
    </div>

    <div class="sm:col-span-2">
        <x-label for="font_stack" value="Font stack" />
        <x-input id="font_stack" name="font_stack" :value="old('font_stack', $theme?->font_stack)" required class="mt-1" placeholder="ui-sans-serif, system-ui, sans-serif" />
        <p class="mt-1 text-xs text-slate-500">Generic font families only (sans-serif/serif/monospace) — no webfont loading.</p>
        <x-input-error :messages="$errors->get('font_stack')" class="mt-1" />
    </div>
</div>

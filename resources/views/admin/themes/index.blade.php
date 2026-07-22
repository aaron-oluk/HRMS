<x-layouts.admin title="Themes" header="Themes">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.themes.create') }}"><x-button icon="bx-plus">Add theme</x-button></a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($themes as $theme)
            <x-card>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">{{ $theme->name }}</h3>
                    @if ($theme->is_default)
                        <x-badge color="success">Default</x-badge>
                    @endif
                </div>
                <div class="flex gap-x-1.5">
                    @foreach (['color_50', 'color_100', 'color_500', 'color_600', 'color_700', 'color_800'] as $shade)
                        <span class="h-6 w-6 rounded-full border border-slate-200" style="background-color: {{ $theme->$shade }}"></span>
                    @endforeach
                </div>
                <p class="mt-3 truncate text-xs text-slate-500" style="font-family: {{ $theme->font_stack }}">{{ $theme->font_stack }}</p>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                    <a href="{{ route('admin.themes.edit', $theme) }}" class="text-sm text-emerald-600 hover:text-emerald-500">Edit</a>
                    @unless ($theme->is_default)
                        <form method="POST" action="{{ route('admin.themes.destroy', $theme) }}" onsubmit="return confirm('Delete {{ $theme->name }}? Tenants using it will fall back to the default theme.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-500">Delete</button>
                        </form>
                    @endunless
                </div>
            </x-card>
        @endforeach
    </div>
</x-layouts.admin>

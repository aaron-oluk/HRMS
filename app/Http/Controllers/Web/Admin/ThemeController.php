<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThemeRequest;
use App\Models\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ThemeController extends Controller
{
    public function index(): View
    {
        return view('admin.themes.index', ['themes' => Theme::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.themes.create');
    }

    public function store(ThemeRequest $request): RedirectResponse
    {
        $theme = Theme::create($request->validated());

        return redirect()->route('admin.themes.index')->with('status', "{$theme->name} was added.");
    }

    public function edit(Theme $theme): View
    {
        return view('admin.themes.edit', ['theme' => $theme]);
    }

    public function update(ThemeRequest $request, Theme $theme): RedirectResponse
    {
        $theme->update($request->validated());

        return redirect()->route('admin.themes.index')->with('status', "{$theme->name} was updated.");
    }

    public function destroy(Theme $theme): RedirectResponse
    {
        if ($theme->is_default) {
            throw ValidationException::withMessages(['theme' => 'The default theme cannot be deleted.']);
        }

        $theme->delete();

        return redirect()->route('admin.themes.index')->with('status', "{$theme->name} was deleted.");
    }
}

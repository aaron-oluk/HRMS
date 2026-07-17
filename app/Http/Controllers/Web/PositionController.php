<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PositionRequest;
use App\Models\Department;
use App\Models\Entity;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PositionController extends Controller
{
    public function index(): View
    {
        Gate::authorize('org.view');

        return view('positions.index', ['positions' => Position::with('entity', 'department')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('org.manage');

        return view('positions.create', [
            'entities' => Entity::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(PositionRequest $request): RedirectResponse
    {
        Position::create($request->validated());

        return redirect()->route('positions.index')->with('status', 'Position created.');
    }

    public function edit(Position $position): View
    {
        Gate::authorize('org.manage');

        return view('positions.edit', [
            'position' => $position,
            'entities' => Entity::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(PositionRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return redirect()->route('positions.index')->with('status', 'Position updated.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        Gate::authorize('org.manage');

        $position->delete();

        return redirect()->route('positions.index')->with('status', 'Position deleted.');
    }
}

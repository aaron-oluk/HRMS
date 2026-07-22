<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AreaRequest;
use App\Models\Area;
use App\Models\Entity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AreaController extends Controller
{
    public function index(): View
    {
        return view('areas.index', ['areas' => Area::with('entity')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('areas.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(AreaRequest $request): RedirectResponse
    {
        Area::create($request->validated());

        return redirect()->route('areas.index')->with('status', 'Area created.');
    }

    public function edit(Area $area): View
    {
        return view('areas.edit', ['area' => $area, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(AreaRequest $request, Area $area): RedirectResponse
    {
        $area->update($request->validated());

        return redirect()->route('areas.index')->with('status', 'Area updated.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        $area->delete();

        return redirect()->route('areas.index')->with('status', 'Area deleted.');
    }
}

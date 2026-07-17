<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntityRequest;
use App\Models\Entity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EntityController extends Controller
{
    public function index(): View
    {
        Gate::authorize('org.view');

        return view('entities.index', ['entities' => Entity::latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('org.manage');

        return view('entities.create');
    }

    public function store(EntityRequest $request): RedirectResponse
    {
        Entity::create($request->validated());

        return redirect()->route('entities.index')->with('status', 'Entity created.');
    }

    public function edit(Entity $entity): View
    {
        Gate::authorize('org.manage');

        return view('entities.edit', ['entity' => $entity]);
    }

    public function update(EntityRequest $request, Entity $entity): RedirectResponse
    {
        $entity->update($request->validated());

        return redirect()->route('entities.index')->with('status', 'Entity updated.');
    }

    public function destroy(Entity $entity): RedirectResponse
    {
        Gate::authorize('org.manage');

        $entity->delete();

        return redirect()->route('entities.index')->with('status', 'Entity deleted.');
    }
}

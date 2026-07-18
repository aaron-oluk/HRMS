<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntityRequest;
use App\Models\Entity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EntityController extends Controller
{
    public function index(): View
    {
        return view('entities.index', ['entities' => Entity::latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('entities.create');
    }

    public function store(EntityRequest $request): RedirectResponse
    {
        Entity::create($request->validated());

        return redirect()->route('entities.index')->with('status', 'Entity created.');
    }

    public function edit(Entity $entity): View
    {
        return view('entities.edit', ['entity' => $entity]);
    }

    public function update(EntityRequest $request, Entity $entity): RedirectResponse
    {
        $entity->update($request->validated());

        return redirect()->route('entities.index')->with('status', 'Entity updated.');
    }

    public function destroy(Entity $entity): RedirectResponse
    {
        $entity->delete();

        return redirect()->route('entities.index')->with('status', 'Entity deleted.');
    }
}

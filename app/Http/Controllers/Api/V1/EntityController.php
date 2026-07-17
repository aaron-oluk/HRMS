<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntityRequest;
use App\Http\Resources\EntityResource;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EntityController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('org.view');

        return EntityResource::collection(Entity::latest()->paginate(25));
    }

    public function store(EntityRequest $request)
    {
        return EntityResource::make(Entity::create($request->validated()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Entity $entity)
    {
        Gate::authorize('org.view');

        return EntityResource::make($entity);
    }

    public function update(EntityRequest $request, Entity $entity)
    {
        $entity->update($request->validated());

        return EntityResource::make($entity);
    }

    public function destroy(Entity $entity)
    {
        Gate::authorize('org.manage');

        $entity->delete();

        return response()->noContent();
    }
}

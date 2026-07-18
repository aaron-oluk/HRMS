<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntityRequest;
use App\Http\Resources\EntityResource;
use App\Models\Entity;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    public function index(Request $request)
    {
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
        return EntityResource::make($entity);
    }

    public function update(EntityRequest $request, Entity $entity)
    {
        $entity->update($request->validated());

        return EntityResource::make($entity);
    }

    public function destroy(Entity $entity)
    {
        $entity->delete();

        return response()->noContent();
    }
}

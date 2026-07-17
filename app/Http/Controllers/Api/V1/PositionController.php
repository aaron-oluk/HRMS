<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PositionRequest;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('org.view');

        return PositionResource::collection(Position::latest()->paginate(25));
    }

    public function store(PositionRequest $request)
    {
        return PositionResource::make(Position::create($request->validated()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Position $position)
    {
        Gate::authorize('org.view');

        return PositionResource::make($position);
    }

    public function update(PositionRequest $request, Position $position)
    {
        $position->update($request->validated());

        return PositionResource::make($position);
    }

    public function destroy(Position $position)
    {
        Gate::authorize('org.manage');

        $position->delete();

        return response()->noContent();
    }
}

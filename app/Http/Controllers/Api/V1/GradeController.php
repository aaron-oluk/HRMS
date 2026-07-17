<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('org.view');

        return GradeResource::collection(Grade::latest()->paginate(25));
    }

    public function store(GradeRequest $request)
    {
        return GradeResource::make(Grade::create($request->validated()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Grade $grade)
    {
        Gate::authorize('org.view');

        return GradeResource::make($grade);
    }

    public function update(GradeRequest $request, Grade $grade)
    {
        $grade->update($request->validated());

        return GradeResource::make($grade);
    }

    public function destroy(Grade $grade)
    {
        Gate::authorize('org.manage');

        $grade->delete();

        return response()->noContent();
    }
}

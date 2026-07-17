<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('org.view');

        return BranchResource::collection(Branch::latest()->paginate(25));
    }

    public function store(BranchRequest $request)
    {
        return BranchResource::make(Branch::create($request->validated()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Branch $branch)
    {
        Gate::authorize('org.view');

        return BranchResource::make($branch);
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return BranchResource::make($branch);
    }

    public function destroy(Branch $branch)
    {
        Gate::authorize('org.manage');

        $branch->delete();

        return response()->noContent();
    }
}

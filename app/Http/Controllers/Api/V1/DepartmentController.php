<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        return DepartmentResource::collection(Department::latest()->paginate(25));
    }

    public function store(DepartmentRequest $request)
    {
        return DepartmentResource::make(Department::create($request->validated()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Department $department)
    {
        return DepartmentResource::make($department);
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return DepartmentResource::make($department);
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return response()->noContent();
    }
}

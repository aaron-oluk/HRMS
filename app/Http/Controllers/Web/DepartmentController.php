<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\Entity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return view('departments.index', ['departments' => Department::with('entity', 'parent')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('departments.create', [
            'entities' => Entity::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated());

        return redirect()->route('departments.index')->with('status', 'Department created.');
    }

    public function edit(Department $department): View
    {
        return view('departments.edit', [
            'department' => $department,
            'entities' => Entity::orderBy('name')->get(),
            'departments' => Department::where('id', '!=', $department->id)->orderBy('name')->get(),
        ]);
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()->route('departments.index')->with('status', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('departments.index')->with('status', 'Department deleted.');
    }
}

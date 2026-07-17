<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\Entity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    public function index(): View
    {
        Gate::authorize('org.view');

        return view('departments.index', ['departments' => Department::with('entity', 'parent')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('org.manage');

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
        Gate::authorize('org.manage');

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
        Gate::authorize('org.manage');

        $department->delete();

        return redirect()->route('departments.index')->with('status', 'Department deleted.');
    }
}

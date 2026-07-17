<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Models\Entity;
use App\Models\Grade;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('org.view');

        return view('grades.index', ['grades' => Grade::with('entity')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('org.manage');

        return view('grades.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(GradeRequest $request): RedirectResponse
    {
        Grade::create($request->validated());

        return redirect()->route('grades.index')->with('status', 'Grade created.');
    }

    public function edit(Grade $grade): View
    {
        Gate::authorize('org.manage');

        return view('grades.edit', ['grade' => $grade, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(GradeRequest $request, Grade $grade): RedirectResponse
    {
        $grade->update($request->validated());

        return redirect()->route('grades.index')->with('status', 'Grade updated.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        Gate::authorize('org.manage');

        $grade->delete();

        return redirect()->route('grades.index')->with('status', 'Grade deleted.');
    }
}

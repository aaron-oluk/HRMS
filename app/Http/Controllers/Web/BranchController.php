<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Models\Entity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class BranchController extends Controller
{
    public function index(): View
    {
        Gate::authorize('org.view');

        return view('branches.index', ['branches' => Branch::with('entity')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('org.manage');

        return view('branches.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        Branch::create($request->validated());

        return redirect()->route('branches.index')->with('status', 'Branch created.');
    }

    public function edit(Branch $branch): View
    {
        Gate::authorize('org.manage');

        return view('branches.edit', ['branch' => $branch, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());

        return redirect()->route('branches.index')->with('status', 'Branch updated.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        Gate::authorize('org.manage');

        $branch->delete();

        return redirect()->route('branches.index')->with('status', 'Branch deleted.');
    }
}

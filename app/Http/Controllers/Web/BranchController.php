<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Models\Entity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('branches.index', ['branches' => Branch::with('entity')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('branches.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        Branch::create($request->validated());

        return redirect()->route('branches.index')->with('status', 'Branch created.');
    }

    public function edit(Branch $branch): View
    {
        return view('branches.edit', ['branch' => $branch, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());

        return redirect()->route('branches.index')->with('status', 'Branch updated.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()->route('branches.index')->with('status', 'Branch deleted.');
    }
}

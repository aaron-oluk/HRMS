<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveTypeRequest;
use App\Models\Entity;
use App\Models\LeaveType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        return view('leave-types.index', ['leaveTypes' => LeaveType::with('entity')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('leave-types.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(LeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::create($request->validated());

        return redirect()->route('leave-types.index')->with('status', 'Leave type created.');
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('leave-types.edit', ['leaveType' => $leaveType, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(LeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $leaveType->update($request->validated());

        return redirect()->route('leave-types.index')->with('status', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $leaveType->delete();

        return redirect()->route('leave-types.index')->with('status', 'Leave type deleted.');
    }
}

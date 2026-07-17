<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftRequest;
use App\Models\Entity;
use App\Models\Shift;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ShiftController extends Controller
{
    public function index(): View
    {
        Gate::authorize('org.view');

        return view('shifts.index', ['shifts' => Shift::with('entity')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('attendance.manage-shifts');

        return view('shifts.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(ShiftRequest $request): RedirectResponse
    {
        Shift::create($request->validated());

        return redirect()->route('shifts.index')->with('status', 'Shift created.');
    }

    public function edit(Shift $shift): View
    {
        Gate::authorize('attendance.manage-shifts');

        return view('shifts.edit', ['shift' => $shift, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(ShiftRequest $request, Shift $shift): RedirectResponse
    {
        $shift->update($request->validated());

        return redirect()->route('shifts.index')->with('status', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        Gate::authorize('attendance.manage-shifts');

        $shift->delete();

        return redirect()->route('shifts.index')->with('status', 'Shift deleted.');
    }
}

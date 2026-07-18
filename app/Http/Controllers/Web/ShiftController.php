<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftRequest;
use App\Models\Entity;
use App\Models\Shift;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShiftController extends Controller
{
    public function index(): View
    {
        return view('shifts.index', ['shifts' => Shift::with('entity')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('shifts.create', ['entities' => Entity::orderBy('name')->get()]);
    }

    public function store(ShiftRequest $request): RedirectResponse
    {
        Shift::create($request->validated());

        return redirect()->route('shifts.index')->with('status', 'Shift created.');
    }

    public function edit(Shift $shift): View
    {
        return view('shifts.edit', ['shift' => $shift, 'entities' => Entity::orderBy('name')->get()]);
    }

    public function update(ShiftRequest $request, Shift $shift): RedirectResponse
    {
        $shift->update($request->validated());

        return redirect()->route('shifts.index')->with('status', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $shift->delete();

        return redirect()->route('shifts.index')->with('status', 'Shift deleted.');
    }
}

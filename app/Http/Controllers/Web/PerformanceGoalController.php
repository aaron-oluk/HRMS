<?php

namespace App\Http\Controllers\Web;

use App\Actions\Performance\UpsertGoal;
use App\Http\Controllers\Controller;
use App\Http\Requests\PerformanceGoalRequest;
use App\Models\PerformanceGoal;
use Illuminate\Http\RedirectResponse;

class PerformanceGoalController extends Controller
{
    public function store(PerformanceGoalRequest $request, UpsertGoal $upsertGoal): RedirectResponse
    {
        $upsertGoal->handle($request->user()->employee, $request->validated());

        return redirect()->route('profile.edit')->with('status', 'Goal added.');
    }

    public function update(PerformanceGoalRequest $request, PerformanceGoal $goal, UpsertGoal $upsertGoal): RedirectResponse
    {
        $upsertGoal->handle($request->user()->employee, $request->validated(), $goal);

        return redirect()->route('profile.edit')->with('status', 'Goal updated.');
    }
}

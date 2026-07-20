<?php

namespace App\Http\Controllers\Web;

use App\Actions\Performance\CreatePerformanceReviewCycle;
use App\Http\Controllers\Controller;
use App\Http\Requests\PerformanceReviewCycleRequest;
use App\Models\Employee;
use App\Models\OneOnOneMeeting;
use App\Models\PerformanceReviewCycle;
use App\Support\Approvals\TeamScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PerformanceReviewCycleController extends Controller
{
    public function index(): View
    {
        return view('performance.cycles.index', [
            'cycles' => PerformanceReviewCycle::withCount('reviews')->latest('start_date')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('performance.cycles.create');
    }

    public function store(PerformanceReviewCycleRequest $request, CreatePerformanceReviewCycle $createPerformanceReviewCycle): RedirectResponse
    {
        $cycle = $createPerformanceReviewCycle->handle($request->validated());

        return redirect()->route('performance.cycles.show', $cycle)->with('status', 'Review cycle created.');
    }

    public function show(Request $request, PerformanceReviewCycle $performanceReviewCycle, TeamScope $teamScope): View
    {
        $user = $request->user();
        $query = $performanceReviewCycle->reviews()->with('employee', 'reviewer', 'feedbackRequests.reviewer');

        if (! $user->can('performance.manage-cycles')) {
            $query = $teamScope->scopeToTeam($query, $user);
        }

        $reviews = $query->get();

        $oneOnOnesQuery = OneOnOneMeeting::with('employee', 'manager')->latest('scheduled_at');
        $oneOnOnes = $user->can('performance.manage-cycles')
            ? $oneOnOnesQuery->get()
            : $teamScope->scopeToTeam($oneOnOnesQuery, $user)->get();

        return view('performance.cycles.show', [
            'cycle' => $performanceReviewCycle,
            'reviews' => $reviews,
            'oneOnOnes' => $oneOnOnes,
            'employees' => Employee::where('status', 'active')->orderBy('first_name')->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PerformanceFeedbackRequest;
use App\Models\PerformanceReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('employee.currentEmployment.department', 'employee.currentEmployment.position', 'employee.entity');
        $employee = $user->employee;

        $payslips = $employee
            ? $employee->payrollRunLines()->with('payrollRun')->get()->sortByDesc(fn ($line) => $line->payrollRun->period_month)->values()
            : collect();

        $performanceReviews = $employee
            ? PerformanceReview::where('employee_id', $employee->id)->with('cycle')->get()->sortByDesc(fn ($review) => $review->cycle->start_date)->values()
            : collect();

        $goals = $employee ? $employee->performanceGoals()->latest()->get() : collect();

        $feedbackRequests = $employee
            ? PerformanceFeedbackRequest::where('reviewer_employee_id', $employee->id)->with('review.employee', 'review.cycle')->latest()->get()
            : collect();

        $oneOnOnes = $employee ? $employee->oneOnOnes()->latest('scheduled_at')->get() : collect();

        return view('profile.edit', [
            'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
            'payslips' => $payslips,
            'performanceReviews' => $performanceReviews,
            'goals' => $goals,
            'feedbackRequests' => $feedbackRequests,
            'oneOnOnes' => $oneOnOnes,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('employee.currentEmployment.department', 'employee.currentEmployment.position', 'employee.entity');

        $payslips = $user->employee
            ? $user->employee->payrollRunLines()->with('payrollRun')->get()->sortByDesc(fn ($line) => $line->payrollRun->period_month)->values()
            : collect();

        $performanceReviews = $user->employee
            ? PerformanceReview::where('employee_id', $user->employee->id)->with('cycle')->get()->sortByDesc(fn ($review) => $review->cycle->start_date)->values()
            : collect();

        return view('profile.edit', [
            'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
            'payslips' => $payslips,
            'performanceReviews' => $performanceReviews,
        ]);
    }
}

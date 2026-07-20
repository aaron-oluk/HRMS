<?php

namespace App\Http\Controllers\Web;

use App\Actions\Cases\AddCaseComment;
use App\Actions\Cases\AssignHrCase;
use App\Actions\Cases\ResolveHrCase;
use App\Actions\Cases\SubmitHrCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\HrCaseCommentRequest;
use App\Http\Requests\HrCaseRequest;
use App\Models\HrCase;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HrCaseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = HrCase::with('employee')->latest();

        if (! $user->can('cases.manage')) {
            $query->where('employee_id', $user->employee?->id);
        }

        return view('cases.index', [
            'cases' => $query->paginate(15),
            'canManage' => $user->can('cases.manage'),
        ]);
    }

    public function create(): View
    {
        return view('cases.create');
    }

    public function store(HrCaseRequest $request, SubmitHrCase $submitHrCase): RedirectResponse
    {
        $case = $submitHrCase->handle($request->user()->employee, $request->validated());

        return redirect()->route('cases.show', $case)->with('status', 'Case submitted.');
    }

    public function show(Request $request, HrCase $case): View
    {
        $user = $request->user();
        $canManage = $user->can('cases.manage');

        abort_unless($canManage || $case->employee_id === $user->employee?->id, 403);

        $comments = $case->comments()->with('author')->when(! $canManage, fn ($q) => $q->where('is_internal', false))->get();

        return view('cases.show', [
            'case' => $case,
            'comments' => $comments,
            'canManage' => $canManage,
            'staff' => $canManage ? User::whereHas('roles', fn ($q) => $q->whereIn('name', ['HR Admin', 'HR Manager', 'HR Specialist']))->get() : collect(),
        ]);
    }

    public function assign(Request $request, HrCase $case, AssignHrCase $assignHrCase): RedirectResponse
    {
        $assignee = User::findOrFail($request->input('assigned_to'));
        $assignHrCase->handle($case, $assignee);

        return redirect()->route('cases.show', $case)->with('status', 'Case assigned.');
    }

    public function resolve(HrCase $case, ResolveHrCase $resolveHrCase): RedirectResponse
    {
        $resolveHrCase->handle($case);

        return redirect()->route('cases.show', $case)->with('status', 'Case resolved.');
    }

    public function comment(HrCaseCommentRequest $request, HrCase $case, AddCaseComment $addCaseComment): RedirectResponse
    {
        $user = $request->user();
        $canManage = $user->can('cases.manage');

        abort_unless($canManage || $case->employee_id === $user->employee?->id, 403);

        $addCaseComment->handle($case, $user, $request->validated('body'), $canManage && $request->boolean('is_internal'));

        return redirect()->route('cases.show', $case)->with('status', 'Comment added.');
    }
}

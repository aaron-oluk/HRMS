<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobRequisitionRequest;
use App\Models\Department;
use App\Models\Entity;
use App\Models\JobRequisition;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobRequisitionController extends Controller
{
    public function index(Request $request): View
    {
        $query = JobRequisition::with('department', 'position')->latest();

        // Department Manager only requested/holds recruitment.view, never recruitment.manage —
        // restrict them to their own department rather than force-fitting TeamScope, which is
        // keyed off employee_id and doesn't apply to a requisition.
        if ($request->user()->hasRole('Department Manager')) {
            $departmentId = $request->user()->employee?->currentEmployment?->department_id;
            $query->where('department_id', $departmentId);
        }

        return view('recruitment.requisitions.index', ['requisitions' => $query->paginate(15)]);
    }

    public function show(Request $request, JobRequisition $jobRequisition): View
    {
        if ($request->user()->hasRole('Department Manager')) {
            $departmentId = $request->user()->employee?->currentEmployment?->department_id;
            abort_unless($jobRequisition->department_id === $departmentId, 403);
        }

        return view('recruitment.requisitions.show', [
            'jobRequisition' => $jobRequisition,
            'candidates' => $jobRequisition->candidates()->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('recruitment.requisitions.create', [
            'entities' => Entity::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('title')->get(),
        ]);
    }

    public function store(JobRequisitionRequest $request): RedirectResponse
    {
        $requisition = JobRequisition::create($request->validated() + [
            'requested_by' => $request->user()->id,
            'opened_at' => $request->validated('status') === 'open' ? now() : null,
        ]);

        return redirect()->route('recruitment.requisitions.show', $requisition)->with('status', 'Requisition created.');
    }

    public function edit(JobRequisition $jobRequisition): View
    {
        return view('recruitment.requisitions.edit', [
            'jobRequisition' => $jobRequisition,
            'entities' => Entity::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('title')->get(),
        ]);
    }

    public function update(JobRequisitionRequest $request, JobRequisition $jobRequisition): RedirectResponse
    {
        $jobRequisition->update($request->validated());

        return redirect()->route('recruitment.requisitions.show', $jobRequisition)->with('status', 'Requisition updated.');
    }
}

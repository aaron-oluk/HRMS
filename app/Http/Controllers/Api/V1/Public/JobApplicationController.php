<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicJobApplicationRequest;
use App\Models\Candidate;
use App\Models\JobRequisition;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class JobApplicationController extends Controller
{
    /**
     * Unauthenticated — no IdentifyTenant middleware ran, so TenantContext is unset here.
     * TenantScope self-disables with no context set (see App\Models\Scopes\TenantScope), so
     * the {jobRequisition} route-model-binding above already resolved across every tenant;
     * this method's job is to validate that's actually a postable job before accepting the
     * application, then set the tenant context so the new Candidate is tagged correctly.
     */
    public function store(PublicJobApplicationRequest $request, JobRequisition $jobRequisition): JsonResponse
    {
        $tenant = Tenant::find($jobRequisition->tenant_id);

        abort_if($tenant === null || $tenant->status !== 'active', 404);
        abort_unless($tenant->hasModule('recruitment'), 404);
        abort_unless($jobRequisition->status === 'open', 404);

        app(TenantContext::class)->set($tenant);

        if (Candidate::where('job_requisition_id', $jobRequisition->id)->where('email', $request->validated('email'))->exists()) {
            throw ValidationException::withMessages([
                'email' => 'You have already applied to this position.',
            ]);
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('candidate-resumes', 'local');
        }

        $jobRequisition->candidates()->create([
            ...$request->safe()->only(['first_name', 'last_name', 'email', 'phone']),
            'source' => $request->validated('source') ?: 'Careers portal',
            'resume_path' => $resumePath,
            'status' => 'applied',
        ]);

        return response()->json(['message' => 'Application received.'], 201);
    }
}

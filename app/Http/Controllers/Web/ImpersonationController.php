<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit\AccessAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Deliberately outside the super-admin middleware group — while impersonating, the
     * authenticated user is the tenant's HR Admin, not a super admin, so that middleware
     * would block this route entirely. Guarded instead by the impersonator_id session key
     * only a real impersonation flow (App\Http\Controllers\Web\Admin\TenantController::impersonate)
     * ever sets.
     */
    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get('impersonator_id');

        abort_unless($impersonatorId, 404);

        $impersonator = User::findOrFail($impersonatorId);
        $target = $request->user();

        AccessAudit::impersonationEnded($impersonator, $target);

        $request->session()->forget('impersonator_id');
        Auth::loginUsingId($impersonator->id);

        return redirect()->route('admin.tenants.index')->with('status', 'Returned to your admin session.');
    }
}

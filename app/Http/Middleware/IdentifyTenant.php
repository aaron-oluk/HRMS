<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected PermissionRegistrar $permissionRegistrar,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant) {
            // A suspended tenant (see App\Actions\Tenancy\SuspendTenant) is enforced here,
            // not just hidden in the UI — every request from one of its users is bounced
            // back to login the moment this middleware runs, tenant-wide.
            if ($user->tenant->status === 'suspended') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => "Your organization's account has been suspended. Contact your platform administrator.",
                ]);
            }

            $this->tenantContext->set($user->tenant);
            $this->permissionRegistrar->setPermissionsTeamId($user->tenant_id);
        }

        return $next($request);
    }
}

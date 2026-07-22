<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminCanAccessTenant
{
    /**
     * Applied only to the admin.tenants.* routes that operate on one specific tenant
     * (show/edit/update/suspend/reactivate/impersonate/modules.update) — a Global super
     * admin always passes; a scoped Org Admin only passes for tenants explicitly assigned
     * to them (see App\Models\User::canAccessTenant()). Runs after SubstituteBindings (see
     * bootstrap/app.php), so the route-bound {tenant} is already resolved here.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        if ($tenant instanceof Tenant && ! $request->user()?->canAccessTenant($tenant)) {
            throw new AuthorizationException('You do not have access to this company.');
        }

        return $next($request);
    }
}

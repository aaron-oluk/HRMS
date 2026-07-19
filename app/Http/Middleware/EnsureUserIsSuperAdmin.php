<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    /**
     * Gates the platform admin console (tenant onboarding). Deliberately its own
     * middleware rather than a role/permission check: super admins hold no role at
     * all (see App\Providers\AppServiceProvider's Gate::before bypass), so 'role:'/
     * 'permission:' middleware can't express "super admin only" — and conversely,
     * this must NOT be reachable by a tenant-scoped HR Admin no matter what they hold.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_super_admin) {
            throw new AuthorizationException('This area is restricted to platform administrators.');
        }

        return $next($request);
    }
}

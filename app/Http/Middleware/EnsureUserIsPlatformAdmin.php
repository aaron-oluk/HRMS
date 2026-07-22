<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPlatformAdmin
{
    /**
     * Gates the general platform admin console — passes for both tiers (Global super admin
     * and scoped Org Admin, see App\Models\User::isPlatformAdmin()). Per-tenant access within
     * the console is enforced separately by App\Http\Middleware\EnsureAdminCanAccessTenant;
     * true global-only actions (onboarding a company, managing other platform admins) stay
     * behind App\Http\Middleware\EnsureUserIsSuperAdmin instead of this one.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isPlatformAdmin()) {
            throw new AuthorizationException('This area is restricted to platform administrators.');
        }

        return $next($request);
    }
}

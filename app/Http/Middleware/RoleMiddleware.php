<?php

namespace App\Http\Middleware;

use Closure;
use Spatie\Permission\Middleware\RoleMiddleware as SpatieRoleMiddleware;

/**
 * Spatie's RoleMiddleware checks role membership directly (via hasAnyRole()) rather
 * than through Laravel's Gate, so it never sees the Gate::before super-admin bypass
 * registered in AppServiceProvider. Short-circuit for super admins here instead.
 */
class RoleMiddleware extends SpatieRoleMiddleware
{
    public function handle($request, Closure $next, $role, $guard = null)
    {
        if ($request->user()?->is_super_admin) {
            return $next($request);
        }

        return parent::handle($request, $next, $role, $guard);
    }
}

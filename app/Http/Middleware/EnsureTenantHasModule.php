<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasModule
{
    /**
     * Backs App\Models\Tenant::hasModule() with real route-level enforcement — the sidebar
     * gate in components/layouts/app.blade.php hides the link, but without this a tenant
     * could still reach a disabled module's routes directly by URL.
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenant = $request->user()?->tenant;

        if ($tenant && ! $tenant->hasModule($module)) {
            throw new AuthorizationException('This feature is not enabled for your organization.');
        }

        return $next($request);
    }
}

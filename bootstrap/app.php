<?php

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnsureAdminCanAccessTenant;
use App\Http\Middleware\EnsureTenantHasModule;
use App\Http\Middleware\EnsureUserIsPlatformAdmin;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\RoleMiddleware;
use App\Support\Audit\AccessAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('web', AssignRequestId::class);
        $middleware->prependToGroup('api', AssignRequestId::class);

        // SubstituteBindings (route model binding) ships as the last entry in the
        // default 'web' group, which would resolve tenant-scoped models BEFORE
        // IdentifyTenant sets the tenant context — silently disabling the tenant
        // scope for route-bound models. Move it to run after IdentifyTenant instead.
        // The 'web' guard is already the default guard, so request()->user() resolves
        // correctly here regardless of whether the route's 'auth' middleware has run yet.
        $middleware->web(remove: [SubstituteBindings::class]);
        $middleware->appendToGroup('web', [IdentifyTenant::class, SubstituteBindings::class]);

        // Same problem on the API side, but there IdentifyTenant also needs auth:sanctum
        // to run first (so Auth::shouldUse('sanctum') takes effect before request()->user()
        // is read) — so the full order is applied at the route group in routes/api_v1.php
        // instead of here.
        $middleware->api(remove: [SubstituteBindings::class]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'super-admin' => EnsureUserIsSuperAdmin::class,
            'platform-admin' => EnsureUserIsPlatformAdmin::class,
            'admin-tenant-access' => EnsureAdminCanAccessTenant::class,
            'tenant-module' => EnsureTenantHasModule::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Both AuthorizationException and Spatie's UnauthorizedException are in Laravel's
        // internal "don't report" list, so ->report() callbacks never fire for them. Log
        // via ->render() instead (always runs), returning null so default rendering proceeds.
        $exceptions->render(function (AuthorizationException $e, $request): void {
            AccessAudit::accessDenied($request->user(), $e->getMessage());
        });

        // Route-level 'permission:' middleware (see routes/web.php, routes/api_v1.php)
        // denies access via Spatie's own exception, not Laravel's AuthorizationException.
        $exceptions->render(function (UnauthorizedException $e, $request): void {
            AccessAudit::accessDenied($request->user(), $e->getMessage());
        });
    })->create();

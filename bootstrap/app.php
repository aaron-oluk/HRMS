<?php

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

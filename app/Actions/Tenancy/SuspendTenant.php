<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;

class SuspendTenant
{
    /**
     * Suspending a tenant doesn't log anyone out immediately — the next request from
     * one of its users is blocked at App\Http\Middleware\IdentifyTenant, which checks
     * the tenant's status and forces a logout there.
     */
    public function handle(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'suspended']);

        return $tenant;
    }
}

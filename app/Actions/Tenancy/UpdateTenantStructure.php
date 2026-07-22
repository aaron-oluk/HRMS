<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;

class UpdateTenantStructure
{
    /**
     * Switching structure is non-destructive in both directions — going back to 'simple'
     * just re-hides Area management and the branch/area-scoped roles, it doesn't delete
     * any Area or Branch records that already exist.
     */
    public function handle(Tenant $tenant, string $structure): Tenant
    {
        $tenant->update(['structure' => $structure]);

        return $tenant;
    }
}

<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use App\Models\TenantFeatureFlag;

class UpdateTenantModules
{
    /**
     * @param  array<int, string>  $enabledModules  Modules present in this list are turned on; every other flaggable module is turned off.
     */
    public function handle(Tenant $tenant, array $enabledModules): void
    {
        foreach (TenantFeatureFlag::MODULES as $module) {
            TenantFeatureFlag::updateOrCreate(
                ['tenant_id' => $tenant->id, 'module' => $module],
                ['enabled' => in_array($module, $enabledModules, true)]
            );
        }
    }
}

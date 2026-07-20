<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;

class UpdateTenant
{
    /**
     * @param  array{name: string, timezone: string, currency: string}  $data
     */
    public function handle(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant;
    }
}

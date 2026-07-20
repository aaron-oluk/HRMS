<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;

class ReactivateTenant
{
    public function handle(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'active']);

        return $tenant;
    }
}

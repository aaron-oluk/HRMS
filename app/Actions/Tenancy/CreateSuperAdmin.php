<?php

namespace App\Actions\Tenancy;

use App\Models\User;

class CreateSuperAdmin
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function handle(array $data): User
    {
        return User::create([
            ...$data,
            'tenant_id' => null,
            'is_super_admin' => true,
        ]);
    }
}

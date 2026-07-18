<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cross-tenant System Admin: bypasses tenant scoping and all permission checks
        // (see Gate::before in AppServiceProvider). No tenant, no role — seeder/tinker only,
        // there is no UI to create these.
        User::firstOrCreate(
            ['email' => 'superadmin@aloflux.test'],
            ['tenant_id' => null, 'name' => 'System Administrator', 'password' => 'password', 'status' => 'active', 'is_super_admin' => true]
        );

        $this->call([
            PermissionSeeder::class,
            DemoTenantSeeder::class,
        ]);
    }
}

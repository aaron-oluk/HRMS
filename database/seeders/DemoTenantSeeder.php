<?php

namespace Database\Seeders;

use App\Actions\Tenancy\ProvisionDefaultRoles;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\Grade;
use App\Models\Position;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'aloflux-demo'],
            ['name' => 'Aloflux Demo Ltd', 'status' => 'active', 'timezone' => 'Africa/Kampala', 'currency' => 'UGX']
        );

        app(TenantContext::class)->set($tenant);

        $roles = app(ProvisionDefaultRoles::class)->handle($tenant);

        $entity = Entity::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Aloflux Demo Ltd'],
            [
                'registration_number' => 'REG-100234',
                'tax_identification_number' => '1000234567',
                'nssf_employer_number' => 'NSSF-77812',
                'address' => 'Plot 14, Acacia Avenue, Kampala',
                'currency' => 'UGX',
                'status' => 'active',
            ]
        );

        $departments = collect(['Human Resources', 'Finance', 'Engineering', 'Sales'])
            ->mapWithKeys(fn (string $name) => [$name => Department::firstOrCreate(
                ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'name' => $name],
                ['code' => strtoupper(substr($name, 0, 3))]
            )]);

        $positions = collect([
            'HR Manager' => 'Human Resources',
            'Payroll Officer' => 'Finance',
            'Software Engineer' => 'Engineering',
            'Sales Executive' => 'Sales',
        ])->mapWithKeys(fn (string $department, string $title) => [$title => Position::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'title' => $title],
            ['department_id' => $departments[$department]->id]
        )]);

        $grade = Grade::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'name' => 'Grade 5'],
            ['level' => 5, 'min_salary' => 1500000, 'max_salary' => 3500000]
        );

        $hrAdminEmployee = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'EMP-00001'],
            [
                'entity_id' => $entity->id,
                'first_name' => 'Grace',
                'last_name' => 'Namu',
                'gender' => 'female',
                'date_of_birth' => '1988-04-12',
                'national_id_number' => 'CM88041200001',
                'nssf_number' => 'NSSF-000001',
                'phone' => '+256700000001',
                'personal_email' => 'grace.namu@example.com',
                'nationality' => 'Ugandan',
                'status' => 'active',
            ]
        );

        Employment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $hrAdminEmployee->id, 'effective_to' => null],
            [
                'entity_id' => $entity->id,
                'department_id' => $departments['Human Resources']->id,
                'position_id' => $positions['HR Manager']->id,
                'grade_id' => $grade->id,
                'basic_salary' => 3200000,
                'effective_from' => now()->subYears(2)->toDateString(),
                'status' => 'active',
                'reason' => 'initial',
            ]
        );

        $hrAdmin = User::firstOrCreate(
            ['email' => 'admin@aloflux-demo.test'],
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $hrAdminEmployee->id,
                'name' => 'Grace Namu',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        $hrAdmin->syncRoles([$roles['HR Admin']]);

        $managerEmployee = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'EMP-00002'],
            [
                'entity_id' => $entity->id,
                'first_name' => 'Peter',
                'last_name' => 'Okello',
                'gender' => 'male',
                'date_of_birth' => '1985-09-03',
                'national_id_number' => 'CM85090300002',
                'nssf_number' => 'NSSF-000002',
                'phone' => '+256700000002',
                'personal_email' => 'peter.okello@example.com',
                'nationality' => 'Ugandan',
                'status' => 'active',
            ]
        );

        Employment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $managerEmployee->id, 'effective_to' => null],
            [
                'entity_id' => $entity->id,
                'department_id' => $departments['Engineering']->id,
                'position_id' => $positions['Software Engineer']->id,
                'grade_id' => $grade->id,
                'basic_salary' => 2800000,
                'effective_from' => now()->subYear()->toDateString(),
                'status' => 'active',
                'reason' => 'initial',
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@aloflux-demo.test'],
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $managerEmployee->id,
                'name' => 'Peter Okello',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        $manager->syncRoles([$roles['Manager']]);

        Employee::factory()
            ->count(6)
            ->for($entity)
            ->create(['tenant_id' => $tenant->id])
            ->each(function (Employee $employee) use ($tenant, $entity, $departments, $positions, $grade): void {
                Employment::factory()->create([
                    'tenant_id' => $tenant->id,
                    'employee_id' => $employee->id,
                    'entity_id' => $entity->id,
                    'department_id' => $departments->random()->id,
                    'position_id' => $positions->random()->id,
                    'grade_id' => $grade->id,
                ]);
            });
    }
}

<?php

namespace App\Actions\Employees;

use App\Models\Employee;

class CreateEmployee
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Employee
    {
        return Employee::create($data);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_run_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employment_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic_salary', 14, 2);
            $table->decimal('gross_pay', 14, 2);
            $table->decimal('paye_amount', 14, 2);
            $table->decimal('nssf_employee_amount', 14, 2);
            $table->decimal('nssf_employer_amount', 14, 2);
            $table->decimal('other_deductions', 14, 2)->default(0);
            $table->decimal('net_pay', 14, 2);
            $table->string('currency', 3)->default('UGX');
            $table->timestamps();
            $table->userstamps();

            $table->unique(['payroll_run_id', 'employee_id']);
            $table->index(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_lines');
    }
};

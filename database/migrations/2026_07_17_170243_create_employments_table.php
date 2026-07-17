<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reporting_to_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employment_type')->default('full_time');
            $table->decimal('basic_salary', 14, 2);
            $table->string('currency', 3)->default('UGX');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status')->default('active')->index();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->userstamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index(['employee_id', 'effective_from']);
        });

        // At most one open-ended active employment row per employee.
        DB::statement(
            'CREATE UNIQUE INDEX employments_one_active_per_employee '.
            'ON employments (employee_id) '.
            "WHERE effective_to IS NULL AND status = 'active'"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('employments');
    }
};

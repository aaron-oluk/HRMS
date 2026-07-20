<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('one_on_one_meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamp('scheduled_at');
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled')->index();
            $table->timestamps();
            $table->userstamps();

            $table->index(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('one_on_one_meetings');
    }
};

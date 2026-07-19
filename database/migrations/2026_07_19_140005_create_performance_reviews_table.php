<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_review_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->unsignedTinyInteger('self_rating')->nullable();
            $table->text('self_comments')->nullable();
            $table->timestamp('self_submitted_at')->nullable();
            $table->unsignedTinyInteger('manager_rating')->nullable();
            $table->text('manager_comments')->nullable();
            $table->timestamp('manager_submitted_at')->nullable();
            $table->timestamps();
            $table->userstamps();

            $table->unique(['performance_review_cycle_id', 'employee_id']);
            $table->index(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};

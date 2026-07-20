<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_feedback_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->userstamps();

            $table->unique(['performance_review_id', 'reviewer_employee_id'], 'peer_feedback_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_feedback_requests');
    }
};

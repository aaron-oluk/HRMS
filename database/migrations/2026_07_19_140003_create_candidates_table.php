<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_requisition_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('advertising')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->userstamps();

            $table->index(['tenant_id', 'job_requisition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};

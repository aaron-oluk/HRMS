<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->decimal('default_days_per_year', 6, 2)->default(0);
            $table->decimal('max_carry_forward_days', 6, 2)->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->userstamps();

            $table->index(['tenant_id', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};

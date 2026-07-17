<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clock_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->timestamp('occurred_at');
            $table->string('source')->default('web');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->uuid('idempotency_key')->nullable();
            $table->timestamps();
            $table->userstamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index(['employee_id', 'occurred_at']);
            $table->unique(['employee_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clock_events');
    }
};

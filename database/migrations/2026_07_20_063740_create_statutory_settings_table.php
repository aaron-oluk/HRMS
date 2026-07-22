<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One row per tenant (unique tenant_id) — requirements vary per organization, so this is
     * deliberately not a shared platform-wide singleton. Every tenant gets its own starting
     * row via App\Actions\Tenancy\SeedDefaultStatutoryConfig, called from
     * App\Actions\Tenancy\CreateTenant, not seeded here.
     */
    public function up(): void
    {
        Schema::create('statutory_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('paye_surcharge_floor', 14, 2);
            $table->decimal('paye_surcharge_rate', 5, 4);
            $table->decimal('nssf_employee_rate', 5, 4);
            $table->decimal('nssf_employer_rate', 5, 4);
            $table->timestamps();
            $table->userstamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_settings');
    }
};

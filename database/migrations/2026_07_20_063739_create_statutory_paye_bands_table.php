<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One tenant's worth of PAYE bands per row — requirements vary per organization, so this
     * is deliberately not a shared platform-wide table. Every tenant gets its own starting set
     * (Uganda's standard published monthly PAYE schedule — see the docblock on
     * App\Support\Payroll\StatutoryEngine) via App\Actions\Tenancy\SeedDefaultStatutoryConfig,
     * called from App\Actions\Tenancy\CreateTenant, not seeded here.
     */
    public function up(): void
    {
        Schema::create('statutory_paye_bands', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->decimal('floor', 14, 2);
            $table->decimal('rate', 5, 4);
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            $table->userstamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_paye_bands');
    }
};

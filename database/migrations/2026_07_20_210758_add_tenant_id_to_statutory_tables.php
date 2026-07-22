<?php

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Statutory config moves from a single platform-wide country pack to one per tenant,
     * since requirements vary per organization. This captures whatever bands/settings exist
     * today (the platform-wide Uganda defaults), then clones them into a fresh set for every
     * existing tenant, so nobody's payroll math changes the moment this migration runs.
     */
    public function up(): void
    {
        $existingBands = DB::table('statutory_paye_bands')->orderBy('order')->get();
        $existingSettings = DB::table('statutory_settings')->first();

        Schema::table('statutory_paye_bands', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->userstamps();
        });

        Schema::table('statutory_settings', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->userstamps();
        });

        DB::table('statutory_paye_bands')->delete();
        DB::table('statutory_settings')->delete();

        $now = now();

        Tenant::all()->each(function (Tenant $tenant) use ($existingBands, $existingSettings, $now): void {
            foreach ($existingBands as $band) {
                DB::table('statutory_paye_bands')->insert([
                    'tenant_id' => $tenant->id,
                    'floor' => $band->floor,
                    'rate' => $band->rate,
                    'order' => $band->order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($existingSettings) {
                DB::table('statutory_settings')->insert([
                    'tenant_id' => $tenant->id,
                    'paye_surcharge_floor' => $existingSettings->paye_surcharge_floor,
                    'paye_surcharge_rate' => $existingSettings->paye_surcharge_rate,
                    'nssf_employee_rate' => $existingSettings->nssf_employee_rate,
                    'nssf_employer_rate' => $existingSettings->nssf_employer_rate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        Schema::table('statutory_paye_bands', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable(false)->change();
        });

        Schema::table('statutory_settings', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable(false)->change();
            $table->unique('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statutory_settings', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id']);
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
        });

        Schema::table('statutory_paye_bands', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // A singleton row (id 1) — platform-wide statutory config, not per-tenant. There is
        // exactly one country pack (Uganda), so there's nothing to key a per-tenant row off of.
        Schema::create('statutory_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('paye_surcharge_floor', 14, 2);
            $table->decimal('paye_surcharge_rate', 5, 4);
            $table->decimal('nssf_employee_rate', 5, 4);
            $table->decimal('nssf_employer_rate', 5, 4);
            $table->timestamps();
        });

        DB::table('statutory_settings')->insert([
            'paye_surcharge_floor' => 10_000_000,
            'paye_surcharge_rate' => 0.10,
            'nssf_employee_rate' => 0.05,
            'nssf_employer_rate' => 0.10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_settings');
    }
};

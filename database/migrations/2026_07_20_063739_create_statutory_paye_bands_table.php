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
        Schema::create('statutory_paye_bands', function (Blueprint $table) {
            $table->id();
            $table->decimal('floor', 14, 2);
            $table->decimal('rate', 5, 4);
            $table->unsignedSmallInteger('order');
            $table->timestamps();
        });

        // Seeds Uganda's standard published monthly PAYE schedule — see the docblock on
        // App\Support\Payroll\StatutoryEngine for where these figures come from. Seeding here
        // (rather than only in a seeder) guarantees the table is never empty after a plain
        // `migrate`, so behavior stays byte-for-byte unchanged from the previous hardcoded bands.
        $now = now();

        DB::table('statutory_paye_bands')->insert([
            ['floor' => 0, 'rate' => 0, 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['floor' => 235_000, 'rate' => 0.10, 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['floor' => 335_000, 'rate' => 0.20, 'order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['floor' => 410_000, 'rate' => 0.30, 'order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_paye_bands');
    }
};

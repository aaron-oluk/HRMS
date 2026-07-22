<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            // Nullable — Simple-structure tenants never set this (see Tenant::isSegmented()).
            $table->foreignId('area_id')->nullable()->after('entity_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('area_id');
        });
    }
};

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
        Schema::table('tenants', function (Blueprint $table): void {
            // Null = the default theme (see App\Models\Theme::default()).
            $table->foreignId('theme_id')->nullable()->after('structure')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('theme_id');
        });
    }
};

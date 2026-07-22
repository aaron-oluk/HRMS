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
        Schema::table('users', function (Blueprint $table): void {
            // A scoped platform admin — sees only the tenants explicitly assigned to them
            // (see platform_admin_tenants) — unlike is_super_admin, which bypasses everything
            // tenant-wide via Gate::before and is left completely untouched by this column.
            $table->boolean('is_org_admin')->default(false)->after('is_super_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_org_admin');
        });
    }
};

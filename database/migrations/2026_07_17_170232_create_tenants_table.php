<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('active')->index();
            // 'simple' (single location) or 'segmented' (Head Office > Area > Branch —
            // see App\Models\Area, App\Models\Tenant::isSegmented()). Changeable any time
            // by the tenant's own HR Admin from Organization Settings.
            $table->string('structure')->default('simple');
            $table->string('timezone')->default('Africa/Kampala');
            $table->string('currency', 3)->default('UGX');
            // Null = the platform default theme (see App\Models\Theme::default()).
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

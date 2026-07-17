<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('registration_number')->nullable();
            $table->string('tax_identification_number')->nullable();
            $table->string('nssf_employer_number')->nullable();
            $table->text('address')->nullable();
            $table->string('currency', 3)->default('UGX');
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->userstamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};

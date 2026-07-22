<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedSmallInteger('level')->default(1);
            $table->decimal('min_salary', 14, 2)->nullable();
            $table->decimal('max_salary', 14, 2)->nullable();
            $table->string('currency', 3)->default('UGX');
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->userstamps();

            $table->index(['tenant_id', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};

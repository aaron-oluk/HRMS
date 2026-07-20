<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status')->default('draft')->index();
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
            $table->userstamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->string('employee_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id_number')->nullable();
            $table->string('nssf_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamp('consent_captured_at')->nullable();
            $table->string('consent_version')->nullable();
            $table->timestamps();
            $table->userstamps();

            $table->unique(['tenant_id', 'employee_number']);
            $table->index(['tenant_id', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

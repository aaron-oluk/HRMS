<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signable_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('signer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('original_path');
            $table->string('signed_path')->nullable();
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('page_count')->default(1);
            $table->unsignedInteger('sign_page_number')->nullable();
            $table->decimal('sign_x', 6, 4)->nullable();
            $table->decimal('sign_y', 6, 4)->nullable();
            $table->decimal('sign_width', 6, 4)->nullable();
            $table->decimal('sign_height', 6, 4)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamps();
            $table->userstamps();

            $table->index(['tenant_id', 'signer_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signable_documents');
    }
};

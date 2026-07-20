<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_response_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating_value')->nullable();
            $table->text('text_value')->nullable();
            $table->timestamps();
            $table->userstamps();

            $table->unique(['survey_response_id', 'survey_question_id'], 'survey_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_response_answers');
    }
};

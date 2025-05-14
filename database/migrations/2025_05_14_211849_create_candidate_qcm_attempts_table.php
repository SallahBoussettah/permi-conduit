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
        Schema::create('candidate_qcm_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('qcm_question_id')->constrained()->onDelete('cascade');
            $table->foreignId('selected_qcm_answer_id')->nullable()->constrained('qcm_answers')->onDelete('set null');
            $table->boolean('is_correct_at_submission')->nullable();
            $table->timestamps();
            
            // Ensure a candidate can only answer each question once per exam
            $table->unique(['exam_id', 'qcm_question_id'], 'exam_question_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_qcm_attempts');
    }
};

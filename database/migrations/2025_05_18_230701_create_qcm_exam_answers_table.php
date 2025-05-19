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
        Schema::create('qcm_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qcm_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('qcm_question_id')->constrained()->onDelete('cascade');
            $table->foreignId('qcm_answer_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
            
            // Ensure a candidate can only answer each question once per exam
            $table->unique(['qcm_exam_id', 'qcm_question_id'], 'qcm_exam_question_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qcm_exam_answers');
    }
};

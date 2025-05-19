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
        Schema::create('qcm_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Candidate
            $table->foreignId('qcm_paper_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->integer('correct_answers_count')->nullable();
            $table->integer('total_questions')->default(10);
            $table->integer('points_earned')->nullable(); // 0-3 points
            $table->boolean('is_eliminatory')->default(false);
            $table->enum('status', ['in_progress', 'completed', 'timed_out'])->default('in_progress');
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qcm_exams');
    }
};

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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('inspector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('exam_type_id')->constrained()->onDelete('cascade');
            $table->date('exam_date');
            $table->enum('status', [
                'pending_qcm', 
                'qcm_taken', 
                'practical_scheduled', 
                'in_progress', 
                'completed',
                'failed'
            ])->default('pending_qcm');
            $table->string('location_details')->nullable();
            $table->timestamp('qcm_passed_at')->nullable();
            $table->integer('qcm_score_correct_answers')->nullable();
            $table->integer('qcm_notation')->nullable(); // Stores 3, 2, 1, or 0 based on QCM rules
            $table->boolean('qcm_is_eliminatory')->default(false);
            $table->text('inspector_notes')->nullable();
            $table->integer('total_points')->nullable();
            $table->boolean('passed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};

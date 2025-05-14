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
        Schema::create('qcm_questions', function (Blueprint $table) {
            $table->id();
            $table->string('qcm_set_id')->nullable(); // For grouping QCMs
            $table->text('question_text');
            $table->foreignId('exam_section_id')->constrained()->onDelete('cascade'); // Linking to "Interrogation Écrite" section
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qcm_questions');
    }
};

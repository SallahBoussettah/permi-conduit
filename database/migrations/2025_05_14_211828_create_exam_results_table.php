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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_item_id')->constrained()->onDelete('cascade');
            $table->string('score_achieved'); // Could be a number (0, 1, 2, 3) or a letter (E)
            $table->text('notes_by_inspector')->nullable();
            $table->timestamps();
            
            // Ensure an exam can only have one result per exam item
            $table->unique(['exam_id', 'exam_item_id'], 'exam_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};

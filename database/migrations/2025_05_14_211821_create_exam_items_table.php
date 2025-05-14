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
        Schema::create('exam_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_section_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->enum('scoring_type', ['points_0_1', 'points_E_0_1_2_3', 'bon_echec']);
            $table->string('reference_in_pdf')->nullable(); // e.g., page number or item code
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_items');
    }
};

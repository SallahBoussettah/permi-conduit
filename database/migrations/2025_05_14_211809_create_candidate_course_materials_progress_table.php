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
        Schema::create('candidate_course_materials_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_material_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'viewed_once'])->default('not_started');
            $table->timestamp('last_accessed_at')->nullable();
            $table->integer('completion_percentage')->default(0);
            $table->timestamps();
            
            // Ensure a user can only have one progress record per course material
            $table->unique(['user_id', 'course_material_id'], 'user_material_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_course_materials_progress');
    }
};

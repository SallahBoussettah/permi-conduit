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
        Schema::table('qcm_questions', function (Blueprint $table) {
            // Drop the old qcm_set_id column
            $table->dropColumn('qcm_set_id');
            
            // Add new columns
            $table->foreignId('qcm_paper_id')->after('id')->constrained()->onDelete('cascade');
            $table->enum('question_type', ['multiple_choice', 'yes_no'])->after('question_text')->default('multiple_choice');
            $table->string('image_path')->nullable()->after('question_type');
            $table->integer('sequence_number')->after('image_path')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qcm_questions', function (Blueprint $table) {
            // Remove new columns
            $table->dropForeign(['qcm_paper_id']);
            $table->dropColumn(['qcm_paper_id', 'question_type', 'image_path', 'sequence_number']);
            
            // Add back the old column
            $table->string('qcm_set_id')->nullable();
        });
    }
};

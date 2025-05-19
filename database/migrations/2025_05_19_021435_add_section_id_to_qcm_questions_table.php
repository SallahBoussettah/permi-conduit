<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, mark the restructure_qcm_questions_system migration as completed
        DB::table('migrations')->insert([
            'migration' => '2025_05_19_014740_restructure_qcm_questions_system',
            'batch' => 4
        ]);

        // Check if section_id column exists in qcm_questions table
        if (!Schema::hasColumn('qcm_questions', 'section_id')) {
            Schema::table('qcm_questions', function (Blueprint $table) {
                $table->unsignedBigInteger('section_id')->nullable()->after('qcm_paper_id');
                $table->foreign('section_id')->references('id')->on('qcm_sections')->onDelete('set null');
            });
        }

        // Also fix other columns that may be missing
        if (!Schema::hasColumn('qcm_questions', 'difficulty')) {
            Schema::table('qcm_questions', function (Blueprint $table) {
                $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->after('question_type');
            });
        }

        if (!Schema::hasColumn('qcm_questions', 'points')) {
            Schema::table('qcm_questions', function (Blueprint $table) {
                $table->integer('points')->default(1)->after('difficulty');
            });
        }

        if (!Schema::hasColumn('qcm_questions', 'explanation')) {
            Schema::table('qcm_questions', function (Blueprint $table) {
                $table->text('explanation')->nullable()->after('image_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is a fix, so we don't need to roll it back
    }
};

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
        // Fix the exam_section_id column by making it nullable
        DB::statement('ALTER TABLE qcm_questions MODIFY exam_section_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This could potentially cause issues if there are null values, 
        // so we're leaving it as nullable in the down method
    }
};

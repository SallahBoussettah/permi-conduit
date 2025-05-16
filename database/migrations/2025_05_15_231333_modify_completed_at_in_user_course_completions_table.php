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
        // First, modify the column to allow NULL values
        DB::statement('ALTER TABLE user_course_completions MODIFY completed_at TIMESTAMP NULL DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to NOT NULL with CURRENT_TIMESTAMP default
        DB::statement('ALTER TABLE user_course_completions MODIFY completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }
};

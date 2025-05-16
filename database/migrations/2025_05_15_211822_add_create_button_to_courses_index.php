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
        Schema::table('courses', function (Blueprint $table) {
            // Check if description column doesn't exist
            if (!Schema::hasColumn('courses', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            
            // Check if exam_section_id column doesn't exist
            if (!Schema::hasColumn('courses', 'exam_section_id')) {
                $table->foreignId('exam_section_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Only drop these columns if they were added by this migration
            if (Schema::hasColumn('courses', 'description')) {
                $table->dropColumn('description');
            }
            
            if (Schema::hasColumn('courses', 'exam_section_id')) {
                $table->dropForeign(['exam_section_id']);
                $table->dropColumn('exam_section_id');
            }
        });
    }
};

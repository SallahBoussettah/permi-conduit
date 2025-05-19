<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Schema\Table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create qcm_sections table
        Schema::create('qcm_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qcm_paper_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sequence_number');
            $table->timestamps();
        });
        
        // Add new columns to qcm_questions table
        Schema::table('qcm_questions', function (Blueprint $table) {
            // Add section_id foreign key
            $table->foreignId('section_id')->nullable()->after('qcm_paper_id');
            
            // Add difficulty level
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->after('question_type');
            
            // Add points field
            $table->integer('points')->default(1)->after('difficulty');
            
            // Add explanation field for the correct answer
            $table->text('explanation')->nullable()->after('image_path');
        });
        
        // Handle the exam_section_id field - make it nullable
        try {
            DB::statement('ALTER TABLE qcm_questions MODIFY exam_section_id BIGINT UNSIGNED NULL');
        } catch (\Exception $e) {
            // Column might not exist or already be nullable
        }
        
        // Create a default section for each existing QCM paper
        $this->createDefaultSections();
        
        // Update existing questions to use the new section_id
        $this->migrateExistingQuestions();
        
        // Add status field to qcm_answers table if it doesn't already exist
        if (Schema::hasTable('qcm_answers') && !Schema::hasColumn('qcm_answers', 'status')) {
            Schema::table('qcm_answers', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('is_correct');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert changes to qcm_questions
        Schema::table('qcm_questions', function (Blueprint $table) {
            if (Schema::hasColumn('qcm_questions', 'section_id')) {
                $table->dropForeign(['section_id']);
                $table->dropColumn('section_id');
            }
            
            if (Schema::hasColumn('qcm_questions', 'difficulty')) {
                $table->dropColumn('difficulty');
            }
            
            if (Schema::hasColumn('qcm_questions', 'points')) {
                $table->dropColumn('points');
            }
            
            if (Schema::hasColumn('qcm_questions', 'explanation')) {
                $table->dropColumn('explanation');
            }
        });
        
        // Revert changes to qcm_answers
        if (Schema::hasTable('qcm_answers') && Schema::hasColumn('qcm_answers', 'status')) {
            Schema::table('qcm_answers', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
        
        // Drop qcm_sections table
        Schema::dropIfExists('qcm_sections');
    }
    
    /**
     * Create a default section for each existing QCM paper
     */
    private function createDefaultSections()
    {
        // Using raw query to get existing QCM papers and create default sections
        $papers = DB::select('SELECT id, title FROM qcm_papers');
        
        foreach ($papers as $paper) {
            DB::table('qcm_sections')->insert([
                'qcm_paper_id' => $paper->id,
                'title' => 'Default Section',
                'description' => 'Default section created during migration',
                'sequence_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    /**
     * Migrate existing questions to use the new section_id
     */
    private function migrateExistingQuestions()
    {
        // Using raw query to move questions to the default sections
        DB::statement('
            UPDATE qcm_questions q
            JOIN qcm_sections s ON q.qcm_paper_id = s.qcm_paper_id
            SET q.section_id = s.id
            WHERE s.title = "Default Section"
        ');
    }
};

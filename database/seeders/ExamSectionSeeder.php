<?php

namespace Database\Seeders;

use App\Models\ExamSection;
use App\Models\ExamType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have at least one exam type
        $examType = ExamType::first();
        
        if (!$examType) {
            // Create a default exam type if none exists
            $examType = ExamType::create([
                'name' => 'Standard Driving Test',
                'description' => 'Standard driving test for category B license',
            ]);
        }
        
        ExamSection::create([
            'exam_type_id' => $examType->id,
            'name' => 'Interrogation Écrite (QCM)',
            'official_max_points' => 10,
            'sequence_order' => 1,
        ]);

        ExamSection::create([
            'exam_type_id' => $examType->id,
            'name' => 'Socle Minimum 1',
            'official_max_points' => 7,
            'sequence_order' => 2,
        ]);

        ExamSection::create([
            'exam_type_id' => $examType->id,
            'name' => 'Socle Minimum 2',
            'official_max_points' => 7,
            'sequence_order' => 3,
        ]);
        
        ExamSection::create([
            'exam_type_id' => $examType->id,
            'name' => 'Socle Minimum 3',
            'official_max_points' => 7,
            'sequence_order' => 4,
        ]);
    }
} 
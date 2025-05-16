<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\ExamSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $examSections = ExamSection::all();
        
        if ($examSections->count() > 0) {
            Course::create([
                'title' => 'Introduction to Driving',
                'description' => 'Basic concepts and principles of safe driving',
                'exam_section_id' => $examSections->first()->id,
            ]);
            
            Course::create([
                'title' => 'Traffic Signs and Signals',
                'description' => 'Understanding and responding to traffic signs and signals',
                'exam_section_id' => $examSections->skip(1)->first()->id,
            ]);
            
            Course::create([
                'title' => 'Vehicle Maintenance',
                'description' => 'Essential maintenance procedures for safe vehicle operation',
                'exam_section_id' => $examSections->skip(2)->first()->id,
            ]);
        } else {
            // Create courses without exam sections if none exist
            Course::create([
                'title' => 'Introduction to Driving',
                'description' => 'Basic concepts and principles of safe driving',
            ]);
            
            Course::create([
                'title' => 'Traffic Signs and Signals',
                'description' => 'Understanding and responding to traffic signs and signals',
            ]);
            
            Course::create([
                'title' => 'Vehicle Maintenance',
                'description' => 'Essential maintenance procedures for safe vehicle operation',
            ]);
        }
    }
} 
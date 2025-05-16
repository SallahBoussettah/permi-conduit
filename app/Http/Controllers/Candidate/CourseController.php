<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\UserCourseProgress;
use App\Models\UserCourseCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $courses = Course::with(['materials', 'completions' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->orderBy('title')->paginate(9);
        
        // Get progress for each course
        foreach ($courses as $course) {
            $totalMaterials = $course->materials->count();
            
            // Get user's completion record for this course
            $completion = $course->completions->where('user_id', $user->id)->first();
            
            if ($completion) {
                $course->progress_percentage = $completion->progress_percentage;
            } else {
                // Calculate progress based on completed materials
                $completedMaterials = 0;
                foreach ($course->materials as $material) {
                    $progress = UserCourseProgress::where('user_id', $user->id)
                        ->where('course_material_id', $material->id)
                        ->where('completed', true)
                        ->first();
                    
                    if ($progress) {
                        $completedMaterials++;
                    }
                }
                
                $course->progress_percentage = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;
                
                // Create or update completion record if needed
                if ($totalMaterials > 0) {
                    UserCourseCompletion::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'course_id' => $course->id
                        ],
                        [
                            'progress_percentage' => $course->progress_percentage,
                            'completed_at' => $course->progress_percentage == 100 ? now() : null
                        ]
                    );
                }
            }
            
            $course->materials_count = $totalMaterials;
        }
        
        return view('candidate.courses.index', compact('courses'));
    }

    /**
     * Display the specified course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\View\View
     */
    public function show(Course $course)
    {
        $user = Auth::user();
        $materials = $course->materials()->orderBy('sequence_order')->get();
        
        // Get progress for each material
        $progress = [];
        foreach ($materials as $material) {
            $userProgress = UserCourseProgress::where('user_id', $user->id)
                ->where('course_material_id', $material->id)
                ->first();
            
            if ($userProgress) {
                $progress[$material->id] = (object)[
                    'completion_percentage' => $userProgress->progress_percentage,
                    'status' => $userProgress->completed ? 'completed' : 
                        ($userProgress->progress_percentage > 0 ? 'in_progress' : 'not_started')
                ];
            } else {
                $progress[$material->id] = (object)[
                    'completion_percentage' => 0,
                    'status' => 'not_started'
                ];
            }
        }
        
        // Calculate overall course progress
        $completion = UserCourseCompletion::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
            
        if (!$completion) {
            $totalMaterials = $materials->count();
            $completedMaterials = 0;
            
            foreach ($materials as $material) {
                if (isset($progress[$material->id]) && $progress[$material->id]->status === 'completed') {
                    $completedMaterials++;
                }
            }
            
            $overallProgress = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;
            
            // Create or update completion record
            $completion = UserCourseCompletion::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'course_id' => $course->id
                ],
                [
                    'progress_percentage' => $overallProgress,
                    'completed_at' => $overallProgress == 100 ? now() : null
                ]
            );
        }
        
        $course->progress_percentage = $completion->progress_percentage;
        
        return view('candidate.courses.show', [
            'course' => $course,
            'courseMaterials' => $materials,
            'progress' => $progress
        ]);
    }
}

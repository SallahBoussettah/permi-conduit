<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\UserCourseProgress;
use App\Models\UserCourseCompletion;
use App\Models\PermitCategory;
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
        $query = Course::with(['materials', 'completions' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);
        
        // Check if user has a permit category and if it's active
        $permitCategoryInactive = false;
        $userPermitCategory = null;
        
        if ($user->permit_category_id) {
            $userPermitCategory = PermitCategory::find($user->permit_category_id);
            
            // Check if the permit category is active
            if ($userPermitCategory && $userPermitCategory->status) {
                $query->where(function($q) use ($user) {
                    $q->where('permit_category_id', $user->permit_category_id)
                      ->orWhereNull('permit_category_id'); // Include courses with no specific permit category
                });
            } else {
                // If permit category is inactive, mark flag and only show general courses
                $permitCategoryInactive = true;
                $query->whereNull('permit_category_id');
            }
        } else {
            // If user doesn't have a permit category, only show courses without a permit category
            $query->whereNull('permit_category_id');
        }
        
        $courses = $query->orderBy('title')->paginate(9);
        
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
        
        return view('candidate.courses.index', compact('courses', 'permitCategoryInactive', 'userPermitCategory'));
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
        
        // Check if the course is associated with a permit category
        if ($course->permit_category_id) {
            // Check if the user has the required permit category
            if ($course->permit_category_id != $user->permit_category_id) {
                return redirect()->route('candidate.courses.index')
                    ->with('error', 'You do not have permission to access this course.');
            }
            
            // Check if the permit category is active
            $permitCategory = PermitCategory::find($course->permit_category_id);
            if (!$permitCategory || !$permitCategory->status) {
                return redirect()->route('candidate.courses.index')
                    ->with('error', 'This course is currently unavailable because its permit category is inactive.');
            }
        }
        
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
        
        $course->progress_percentage = $completion->progress_percentage;
        
        return view('candidate.courses.show', [
            'course' => $course,
            'courseMaterials' => $materials,
            'progress' => $progress
        ]);
    }
}

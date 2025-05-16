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
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Course::with(['materials', 'completions' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);
        
        // Get user's permit categories
        $userPermitCategories = $user->permitCategories;
        $permitCategoryIds = $userPermitCategories->pluck('id')->toArray();
        
        // Check if user has any active permit categories
        $activePermitCategories = $userPermitCategories->where('status', true);
        $hasActivePermitCategories = $activePermitCategories->count() > 0;
        
        // Display inactive permit category warning if any categories are inactive
        $permitCategoryInactive = $userPermitCategories->count() > 0 && $activePermitCategories->count() < $userPermitCategories->count();
        $userPermitCategory = $userPermitCategories->first(); // For backward compatibility with the view
        
        // Handle permit category filter
        $selectedPermitCategory = $request->input('permit_category');
        
        if ($selectedPermitCategory === 'null') {
            // User wants to see only general courses (no permit category)
            $query->whereNull('permit_category_id');
        } elseif (!empty($selectedPermitCategory)) {
            // User selected a specific permit category
            // Verify the user has access to this category and it's active
            if (in_array($selectedPermitCategory, $activePermitCategories->pluck('id')->toArray())) {
                $query->where('permit_category_id', $selectedPermitCategory);
            } else {
                // Fallback to default behavior if user doesn't have access to selected category
                $this->applyDefaultCategoryFilter($query, $hasActivePermitCategories, $activePermitCategories);
            }
        } else {
            // No specific filter applied, use default behavior
            $this->applyDefaultCategoryFilter($query, $hasActivePermitCategories, $activePermitCategories);
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
        
        return view('candidate.courses.index', compact('courses', 'permitCategoryInactive', 'userPermitCategory', 'activePermitCategories', 'userPermitCategories'));
    }

    /**
     * Apply the default permit category filter logic
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $hasActivePermitCategories
     * @param \Illuminate\Support\Collection $activePermitCategories
     * @return void
     */
    private function applyDefaultCategoryFilter($query, $hasActivePermitCategories, $activePermitCategories)
    {
        if ($hasActivePermitCategories) {
            // Show courses that match user's active permit categories or have no permit category
            $activePermitCategoryIds = $activePermitCategories->pluck('id')->toArray();
            $query->where(function($q) use ($activePermitCategoryIds) {
                $q->whereIn('permit_category_id', $activePermitCategoryIds)
                  ->orWhereNull('permit_category_id'); // Include courses with no specific permit category
            });
        } else {
            // If no active permit categories, only show courses without a permit category
            $query->whereNull('permit_category_id');
        }
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
            if (!$user->hasPermitCategory($course->permit_category_id)) {
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

<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\UserCourseProgress;
use App\Models\UserCourseCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class CourseMaterialController extends Controller
{
    /**
     * Display the specified material.
     *
     * @param  \App\Models\Course  $course
     * @param  \App\Models\CourseMaterial  $material
     * @return \Illuminate\View\View
     */
    public function show(Course $course, CourseMaterial $material)
    {
        $user = Auth::user();
        
        // Get user's progress for this material
        $progress = UserCourseProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_material_id' => $material->id
            ],
            [
                'progress_percentage' => 0,
                'last_page' => 1,
                'completed' => false
            ]
        );
        
        // Get next and previous materials
        $nextMaterial = $course->materials()
            ->where('sequence_order', '>', $material->sequence_order)
            ->orderBy('sequence_order')
            ->first();
            
        $prevMaterial = $course->materials()
            ->where('sequence_order', '<', $material->sequence_order)
            ->orderBy('sequence_order', 'desc')
            ->first();
        
        // Choose appropriate view based on material type
        if ($material->material_type === 'video') {
            return view('candidate.courses.materials.show-video', compact(
                'course', 
                'material', 
                'progress', 
                'nextMaterial', 
                'prevMaterial'
            ));
        } else {
            // Default view for PDFs
            return view('candidate.courses.materials.show', compact(
                'course', 
                'material', 
                'progress', 
                'nextMaterial', 
                'prevMaterial'
            ));
        }
    }

    /**
     * Serve the PDF file.
     *
     * @param  \App\Models\Course  $course
     * @param  \App\Models\CourseMaterial  $material
     * @return \Illuminate\Http\Response
     */
    public function servePdf(Course $course, CourseMaterial $material)
    {
        // Check if file exists
        $filePath = 'public/pdfs/' . $material->content_path_or_url;
        
        if (!Storage::exists($filePath)) {
            abort(404, 'PDF file not found');
        }
        
        // Get file content
        $fileContent = Storage::get($filePath);
        
        // Return file as response
        return Response::make($fileContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $material->title . '.pdf"',
        ]);
    }

    /**
     * Update progress for a material.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @param  \App\Models\CourseMaterial  $material
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProgress(Request $request, Course $course, CourseMaterial $material)
    {
        // Different validation rules based on material type
        if ($material->material_type === 'video') {
            $request->validate([
                'progress_percentage' => 'required|numeric|min:0|max:100',
            ]);
            
            $lastPage = 1; // Not applicable for videos
        } else {
            $request->validate([
                'page' => 'required|integer|min:1',
                'progress_percentage' => 'required|numeric|min:0|max:100',
            ]);
            
            $lastPage = $request->page;
        }
        
        $user = Auth::user();
        
        // Update or create progress record
        $progress = UserCourseProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_material_id' => $material->id,
            ],
            [
                'last_page' => $lastPage,
                'progress_percentage' => $request->progress_percentage,
                'completed' => $request->progress_percentage >= 100,
            ]
        );
        
        // Update course completion record
        $this->updateCourseCompletion($user, $course);
        
        return response()->json([
            'success' => true,
            'progress' => $progress->progress_percentage,
            'completed' => $progress->completed,
        ]);
    }

    /**
     * Mark a material as complete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @param  \App\Models\CourseMaterial  $material
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsComplete(Request $request, Course $course, CourseMaterial $material)
    {
        $user = Auth::user();
        
        // Set appropriate last_page based on material type
        $lastPage = ($material->material_type === 'pdf') ? ($material->page_count ?? 1) : 1;
        
        // Mark as complete
        $progress = UserCourseProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_material_id' => $material->id,
            ],
            [
                'last_page' => $lastPage,
                'progress_percentage' => 100,
                'completed' => true,
            ]
        );
        
        // Update course completion record
        $this->updateCourseCompletion($user, $course);
        
        return redirect()->back()->with('success', 'Material marked as complete!');
    }
    
    /**
     * Update the course completion record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Course  $course
     * @return \App\Models\UserCourseCompletion
     */
    private function updateCourseCompletion($user, $course)
    {
        // Calculate overall course progress
        $totalMaterials = $course->materials()->count();
        $completedMaterials = UserCourseProgress::where('user_id', $user->id)
            ->whereIn('course_material_id', $course->materials()->pluck('id'))
            ->where('completed', true)
            ->count();
        
        $progressPercentage = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;
        $isCompleted = $progressPercentage == 100;
        
        // Update course completion record
        return UserCourseCompletion::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'progress_percentage' => $progressPercentage,
                'completed_at' => $isCompleted ? now() : null,
            ]
        );
    }
}

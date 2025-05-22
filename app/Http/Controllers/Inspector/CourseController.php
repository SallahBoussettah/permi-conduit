<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ExamSection;
use App\Models\CourseCategory;
use App\Models\PermitCategory;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * The notification service instance.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the courses.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $courses = Course::orderBy('title')->paginate(10);
        return view('inspector.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $examSections = ExamSection::orderBy('name')->pluck('name', 'id');
        $categories = CourseCategory::where('status', true)->orderBy('name')->pluck('name', 'id');
        $permitCategories = PermitCategory::where('status', true)->orderBy('name')->pluck('name', 'id');
        return view('inspector.courses.create', compact('examSections', 'categories', 'permitCategories'));
    }

    /**
     * Store a newly created course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exam_section_id' => 'nullable|exists:exam_sections,id',
            'category_id' => 'nullable|exists:course_categories,id',
            'permit_category_id' => 'nullable|exists:permit_categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = time() . '_' . Str::slug($request->title) . '.' . $thumbnail->getClientOriginalExtension();
            $thumbnail->storeAs('thumbnails', $thumbnailName, 'public');
            $validated['thumbnail'] = 'thumbnails/' . $thumbnailName;
        }

        // Add the authenticated inspector as the course creator
        $validated['inspector_id'] = Auth::id();
        $validated['created_by'] = Auth::id();
        $validated['status'] = true;

        $course = Course::create($validated);

        // Always send notifications for new courses, even if they don't have a permit category
        // The NotificationService will handle the logic for determining who gets notified
        $this->notificationService->notifyNewCourse($course);

        return redirect()->route('inspector.courses.show', $course)
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\View\View
     */
    public function show(Course $course)
    {
        $materials = $course->materials()->orderBy('sequence_order')->get();
        return view('inspector.courses.show', compact('course', 'materials'));
    }

    /**
     * Show the form for editing the specified course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\View\View
     */
    public function edit(Course $course)
    {
        $examSections = ExamSection::orderBy('name')->pluck('name', 'id');
        $categories = CourseCategory::where('status', true)->orderBy('name')->pluck('name', 'id');
        $permitCategories = PermitCategory::where('status', true)->orderBy('name')->pluck('name', 'id');
        return view('inspector.courses.edit', compact('course', 'examSections', 'categories', 'permitCategories'));
    }

    /**
     * Update the specified course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exam_section_id' => 'nullable|exists:exam_sections,id',
            'category_id' => 'nullable|exists:course_categories,id',
            'permit_category_id' => 'nullable|exists:permit_categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = time() . '_' . Str::slug($request->title) . '.' . $thumbnail->getClientOriginalExtension();
            $thumbnail->storeAs('thumbnails', $thumbnailName, 'public');
            $validated['thumbnail'] = 'thumbnails/' . $thumbnailName;
        }

        // Add the authenticated inspector as the course updater
        $validated['updated_by'] = Auth::id();

        $course->update($validated);

        return redirect()->route('inspector.courses.show', $course)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Course $course)
    {
        // Check if the course has materials
        if ($course->materials()->count() > 0) {
            return redirect()->route('inspector.courses.show', $course)
                ->with('error', 'Cannot delete course with materials. Please remove all materials first.');
        }

        // Delete thumbnail if exists
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        // Soft delete with deleted_by
        $course->update(['deleted_by' => Auth::id()]);
        $course->delete();

        return redirect()->route('inspector.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}

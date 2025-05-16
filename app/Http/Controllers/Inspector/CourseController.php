<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ExamSection;
use Illuminate\Http\Request;

class CourseController extends Controller
{
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
        return view('inspector.courses.create', compact('examSections'));
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
        ]);

        $course = Course::create($validated);

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
        return view('inspector.courses.edit', compact('course', 'examSections'));
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
        ]);

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

        $course->delete();

        return redirect()->route('inspector.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}

<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a notification for a new course
     *
     * @param Course $course
     * @return void
     */
    public function notifyNewCourse(Course $course)
    {
        Log::info('Generating notifications for new course', [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'permit_category_id' => $course->permit_category_id
        ]);

        // Find candidate role ID
        $candidateRoleId = Role::where('name', 'candidate')->value('id');
        
        if (!$candidateRoleId) {
            Log::error('Could not find candidate role ID');
            return;
        }

        $usersQuery = User::where('role_id', $candidateRoleId);
        
        // If course has permit category, only notify users with that category
        if ($course->permit_category_id) {
            $usersQuery->whereHas('permitCategories', function ($query) use ($course) {
                $query->where('permit_categories.id', $course->permit_category_id);
            });
            Log::info('Filtering users by permit category', ['permit_category_id' => $course->permit_category_id]);
        } else {
            // If course has no permit category, notify all candidates
            Log::info('Course has no permit category, notifying all candidates');
        }
        
        $users = $usersQuery->get();

        Log::info('Found candidates for notification', ['count' => $users->count()]);

        foreach ($users as $user) {
            try {
                $notification = Notification::create([
                    'user_id' => $user->id,
                    'message' => "New course available: {$course->title}",
                    'link' => route('candidate.courses.show', $course),
                    'read_at' => null,
                ]);
                
                Log::info('Created notification', [
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification', [
                    'course_id' => $course->id, 
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Notify users about incomplete courses
     *
     * @param int $daysSinceLastProgress
     * @return void
     */
    public function notifyIncompleteCourses($daysSinceLastProgress = 7)
    {
        $candidates = User::whereHas('role', function ($query) {
            $query->where('name', 'candidate');
        })->get();

        foreach ($candidates as $candidate) {
            // Get courses with incomplete materials
            $incompleteCourses = $this->getIncompleteCourses($candidate, $daysSinceLastProgress);
            
            foreach ($incompleteCourses as $course) {
                // Check if there's already a notification for this course in the last week
                $existingNotification = Notification::where('user_id', $candidate->id)
                    ->where('message', 'like', "Don't forget to complete: {$course->title}%")
                    ->where('created_at', '>', Carbon::now()->subDays(7))
                    ->exists();
                
                if (!$existingNotification) {
                    Notification::create([
                        'user_id' => $candidate->id,
                        'message' => "Don't forget to complete: {$course->title}",
                        'link' => route('candidate.courses.show', $course),
                        'read_at' => null,
                    ]);
                }
            }
        }
    }

    /**
     * Notify users about upcoming exams
     *
     * @param int $daysBeforeExam
     * @return void
     */
    public function notifyUpcomingExams($daysBeforeExam = 3)
    {
        $upcomingExams = \App\Models\Exam::where('exam_date', '>', Carbon::now())
            ->where('exam_date', '<=', Carbon::now()->addDays($daysBeforeExam))
            ->whereNull('qcm_is_eliminatory') // Don't notify about exams with eliminatory QCM
            ->get();

        foreach ($upcomingExams as $exam) {
            // Check if there's already a notification for this exam
            $existingNotification = Notification::where('user_id', $exam->candidate_id)
                ->where('message', 'like', "Upcoming exam on%")
                ->where('link', 'like', "%exam%{$exam->id}%")
                ->exists();
            
            if (!$existingNotification) {
                Notification::create([
                    'user_id' => $exam->candidate_id,
                    'message' => "Upcoming exam on " . Carbon::parse($exam->exam_date)->format('d/m/Y'),
                    'link' => route('dashboard'), // Should point to exam details page when implemented
                    'read_at' => null,
                ]);
            }
        }
    }

    /**
     * Get courses with incomplete materials for a candidate
     *
     * @param User $candidate
     * @param int $daysSinceLastProgress
     * @return \Illuminate\Support\Collection
     */
    private function getIncompleteCourses(User $candidate, $daysSinceLastProgress)
    {
        $incompleteCourses = collect();
        
        // Get all courses for this candidate's permit categories
        $courses = Course::whereIn('permit_category_id', $candidate->permitCategories->pluck('id'))->get();
        
        foreach ($courses as $course) {
            // Check if course is incomplete (has materials with no progress or incomplete status)
            $hasIncompleteMaterials = $course->materials()
                ->whereDoesntHave('progress', function ($query) use ($candidate) {
                    $query->where('user_id', $candidate->id)
                        ->whereIn('status', ['completed', 'viewed_once']);
                })
                ->exists();
            
            // Check if there's been no progress in X days
            $lastProgress = $candidate->courseMaterialProgress()
                ->whereHas('courseMaterial', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->orderBy('updated_at', 'desc')
                ->first();
            
            $noRecentProgress = !$lastProgress || $lastProgress->updated_at->diffInDays(Carbon::now()) >= $daysSinceLastProgress;
            
            if ($hasIncompleteMaterials && $noRecentProgress) {
                $incompleteCourses->push($course);
            }
        }
        
        return $incompleteCourses;
    }
} 
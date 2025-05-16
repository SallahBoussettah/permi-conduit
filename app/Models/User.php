<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the permit categories that this user belongs to.
     */
    public function permitCategories()
    {
        return $this->belongsToMany(PermitCategory::class, 'user_permit_categories')
            ->withTimestamps();
    }

    /**
     * For backward compatibility, get the primary permit category (first one)
     */
    public function permitCategory(): BelongsTo
    {
        // This is kept for backward compatibility
        // It will return a relationship that mimics a BelongsTo
        // but actually gets the first permit category from the many-to-many relationship
        return $this->belongsTo(PermitCategory::class, 'id', 'id')
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('user_permit_categories')
                    ->whereColumn('user_permit_categories.user_id', 'users.id')
                    ->whereColumn('user_permit_categories.permit_category_id', 'permit_categories.id');
            });
    }

    /**
     * Check if user has a specific permit category.
     */
    public function hasPermitCategory($permitCategoryId): bool
    {
        return $this->permitCategories()->where('permit_categories.id', $permitCategoryId)->exists();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role->name === $roleName;
    }

    /**
     * Get the exams where the user is a candidate.
     */
    public function candidateExams(): HasMany
    {
        return $this->hasMany(Exam::class, 'candidate_id');
    }

    /**
     * Get the exams where the user is an inspector.
     */
    public function inspectorExams(): HasMany
    {
        return $this->hasMany(Exam::class, 'inspector_id');
    }

    /**
     * Get the course material progress records for the user.
     */
    public function courseMaterialProgress(): HasMany
    {
        return $this->hasMany(CandidateCourseMaterialProgress::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function customNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the course materials that the user has progress on.
     */
    public function courseMaterialsProgress()
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    /**
     * Get the completed course materials for the user.
     */
    public function completedMaterials()
    {
        return $this->hasMany(UserCourseProgress::class)->where('completed', true);
    }

    /**
     * Get the completed courses for the user.
     */
    public function completedCourses()
    {
        return $this->belongsToMany(Course::class, 'user_course_completions')
            ->withTimestamps();
    }

    /**
     * Check if the user has completed a specific course.
     *
     * @param  \App\Models\Course|int  $course
     * @return bool
     */
    public function hasCompletedCourse($course)
    {
        $courseId = $course instanceof Course ? $course->id : $course;
        
        return $this->completedCourses()->where('course_id', $courseId)->exists();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name}";
    }

    // Course progress relationship
    public function courseProgress()
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    // Course completions relationship - using the existing completedCourses relationship
    public function courseCompletions()
    {
        return $this->completedCourses();
    }

    // Get progress for a specific course
    public function getProgressForCourse($courseId)
    {
        return $this->completedCourses()
            ->where('course_id', $courseId)
            ->first();
    }
}

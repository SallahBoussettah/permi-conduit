<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'category_id',
        'inspector_id',
        'thumbnail',
        'permit_category_id',
        'school_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the exam section that this course belongs to.
     */
    public function examSection(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class);
    }

    /**
     * Get the materials for this course.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class);
    }

    /**
     * Get the users who have completed this course.
     */
    public function completedUsers()
    {
        return $this->belongsToMany(User::class, 'user_course_completions')
            ->withTimestamps();
    }

    /**
     * Check if the course is completed by a specific user.
     *
     * @param  \App\Models\User|int  $user
     * @return bool
     */
    public function isCompletedBy($user)
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        if ($this->materials()->count() === 0) {
            return false;
        }
        
        $completedCount = UserCourseProgress::where('user_id', $userId)
            ->whereIn('course_material_id', $this->materials()->pluck('id'))
            ->where('completed', true)
            ->count();
            
        return $completedCount === $this->materials()->count();
    }

    /**
     * Get the completion percentage for a specific user.
     *
     * @param  \App\Models\User|int  $user
     * @return float
     */
    public function getCompletionPercentage($user)
    {
        $userId = $user instanceof User ? $user->id : $user;
        $totalMaterials = $this->materials()->count();
        
        if ($totalMaterials === 0) {
            return 0;
        }
        
        $completedCount = UserCourseProgress::where('user_id', $userId)
            ->whereIn('course_material_id', $this->materials()->pluck('id'))
            ->where('completed', true)
            ->count();
            
        return ($completedCount / $totalMaterials) * 100;
    }

    // Relationships
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function permitCategory(): BelongsTo
    {
        return $this->belongsTo(PermitCategory::class, 'permit_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function exam(): HasOne
    {
        return $this->hasOne(Exam::class);
    }

    // Add relationship for user course completions
    public function completions(): HasMany
    {
        return $this->hasMany(UserCourseCompletion::class);
    }

    /**
     * Get the school that this course belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        return asset('images/default-course-thumbnail.jpg');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class CourseMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'material_type',
        'content_path_or_url',
        'thumbnail_path',
        'page_count',
        'sequence_order',
    ];

    /**
     * Get the course that owns this material.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the users who have progress on this material.
     */
    public function userProgress()
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    /**
     * Check if the material is completed by a specific user.
     *
     * @param  \App\Models\User|int  $user
     * @return bool
     */
    public function isCompletedBy($user)
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        return UserCourseProgress::where('user_id', $userId)
            ->where('course_material_id', $this->id)
            ->where('completed', true)
            ->exists();
    }

    /**
     * Get the progress percentage for a specific user.
     *
     * @param  \App\Models\User|int  $user
     * @return float
     */
    public function getProgressPercentage($user)
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        $progress = UserCourseProgress::where('user_id', $userId)
            ->where('course_material_id', $this->id)
            ->first();
            
        return $progress ? $progress->progress_percentage : 0;
    }

    /**
     * Get the last page read by a specific user.
     *
     * @param  \App\Models\User|int  $user
     * @return int
     */
    public function getLastPage($user)
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        $progress = UserCourseProgress::where('user_id', $userId)
            ->where('course_material_id', $this->id)
            ->first();
            
        return $progress ? $progress->last_page : 1;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCourseCompletion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'progress_percentage',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'progress_percentage' => 'float',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns this completion record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that this completion record belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

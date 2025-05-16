<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCourseProgress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'course_material_id',
        'progress_percentage',
        'last_page',
        'completed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'progress_percentage' => 'float',
        'last_page' => 'integer',
        'completed' => 'boolean',
    ];

    /**
     * Get the user that owns this progress record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course material that this progress record belongs to.
     */
    public function courseMaterial()
    {
        return $this->belongsTo(CourseMaterial::class);
    }
} 
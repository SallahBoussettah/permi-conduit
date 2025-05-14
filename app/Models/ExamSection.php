<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_type_id',
        'name',
        'official_max_points',
        'sequence_order',
    ];

    /**
     * Get the exam type that owns the exam section.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * Get the exam items for the exam section.
     */
    public function examItems(): HasMany
    {
        return $this->hasMany(ExamItem::class);
    }

    /**
     * Get the courses for the exam section.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get the QCM questions for the exam section.
     */
    public function qcmQuestions(): HasMany
    {
        return $this->hasMany(QcmQuestion::class);
    }
}

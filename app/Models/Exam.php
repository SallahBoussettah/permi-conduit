<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'candidate_id',
        'inspector_id',
        'exam_type_id',
        'exam_date',
        'status',
        'location_details',
        'qcm_passed_at',
        'qcm_score_correct_answers',
        'qcm_notation',
        'qcm_is_eliminatory',
        'inspector_notes',
        'total_points',
        'passed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exam_date' => 'date',
        'qcm_passed_at' => 'datetime',
        'qcm_is_eliminatory' => 'boolean',
        'passed' => 'boolean',
    ];

    /**
     * Get the candidate that owns the exam.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    /**
     * Get the inspector that owns the exam.
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Get the exam type that owns the exam.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * Get the exam results for the exam.
     */
    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * Get the QCM attempts for the exam.
     */
    public function qcmAttempts(): HasMany
    {
        return $this->hasMany(CandidateQcmAttempt::class);
    }
}

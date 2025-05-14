<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateQcmAttempt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_id',
        'qcm_question_id',
        'selected_qcm_answer_id',
        'is_correct_at_submission',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_correct_at_submission' => 'boolean',
    ];

    /**
     * Get the exam that owns the QCM attempt.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the QCM question that owns the QCM attempt.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QcmQuestion::class, 'qcm_question_id');
    }

    /**
     * Get the selected QCM answer for the QCM attempt.
     */
    public function selectedAnswer(): BelongsTo
    {
        return $this->belongsTo(QcmAnswer::class, 'selected_qcm_answer_id');
    }
}

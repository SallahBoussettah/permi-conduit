<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcmExamAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'qcm_exam_id',
        'qcm_question_id',
        'qcm_answer_id',
        'is_correct',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the exam that owns the answer.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(QcmExam::class, 'qcm_exam_id');
    }

    /**
     * Get the question that owns the answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QcmQuestion::class, 'qcm_question_id');
    }

    /**
     * Get the selected answer.
     */
    public function selectedAnswer(): BelongsTo
    {
        return $this->belongsTo(QcmAnswer::class, 'qcm_answer_id');
    }
}

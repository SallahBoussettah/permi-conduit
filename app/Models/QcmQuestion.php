<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcmQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'qcm_set_id',
        'question_text',
        'exam_section_id',
    ];

    /**
     * Get the exam section that owns the QCM question.
     */
    public function examSection(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class);
    }

    /**
     * Get the QCM answers for the QCM question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QcmAnswer::class);
    }

    /**
     * Get the QCM attempts for the QCM question.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(CandidateQcmAttempt::class);
    }
}

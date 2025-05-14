<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcmAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'qcm_question_id',
        'answer_text',
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
     * Get the QCM question that owns the QCM answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QcmQuestion::class, 'qcm_question_id');
    }

    /**
     * Get the QCM attempts for the QCM answer.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(CandidateQcmAttempt::class, 'selected_qcm_answer_id');
    }
}

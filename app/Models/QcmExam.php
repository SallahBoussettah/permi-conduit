<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcmExam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'qcm_paper_id',
        'started_at',
        'completed_at',
        'duration_seconds',
        'correct_answers_count',
        'total_questions',
        'points_earned',
        'is_eliminatory',
        'status',
        'school_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_eliminatory' => 'boolean',
    ];

    /**
     * Get the user (candidate) that owns the QCM exam.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the paper for the QCM exam.
     */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(QcmPaper::class, 'qcm_paper_id');
    }

    /**
     * Get the answers for the QCM exam.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QcmExamAnswer::class);
    }

    /**
     * Get the school that owns the QCM exam.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Check if the exam is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the exam is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the exam timed out.
     */
    public function isTimedOut(): bool
    {
        return $this->status === 'timed_out';
    }

    /**
     * Check if the exam is passed.
     */
    public function isPassed(): bool
    {
        return $this->points_earned > 0 && !$this->is_eliminatory;
    }

    /**
     * Get the remaining time in seconds.
     */
    public function getRemainingTimeInSeconds(): int
    {
        if ($this->completed_at) {
            return 0;
        }

        $maxDuration = 360; // 6 minutes in seconds
        $elapsedTime = now()->diffInSeconds($this->started_at);
        
        return max(0, $maxDuration - $elapsedTime);
    }
}

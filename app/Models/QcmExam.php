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
        'expires_at',
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
        'expires_at' => 'datetime',
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
        // If the exam is already completed, there's no time remaining
        if ($this->completed_at) {
            \Log::info("Exam {$this->id} is already completed, no time remaining");
            return 0;
        }

        // If expires_at is not set, calculate it based on started_at
        if (!$this->expires_at && $this->started_at) {
            $this->expires_at = $this->started_at->copy()->addMinutes(6); // 6 minutes (360 seconds)
            $this->save();
            \Log::info("Set expires_at for exam {$this->id} to {$this->expires_at}");
        }
        
        // If we still don't have expires_at, something is wrong - default to no time
        if (!$this->expires_at) {
            \Log::error("Exam {$this->id} has no expires_at time and no started_at time");
            return 0;
        }
        
        // Calculate remaining time based on expires_at
        $now = now();
        
        // If current time is past the expiration time, return 0
        if ($now->gt($this->expires_at)) {
            \Log::info("Exam {$this->id} has expired at {$this->expires_at}, current time is {$now}");
            return 0;
        }
        
        // Calculate the remaining seconds - correct order: time from now until expires_at
        $remaining = $now->diffInSeconds($this->expires_at);
        \Log::info("Exam {$this->id} has {$remaining} seconds remaining until {$this->expires_at}, current time: {$now}");
        
        return $remaining;
    }
}

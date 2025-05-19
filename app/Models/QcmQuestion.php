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
        'qcm_paper_id',
        'section_id',
        'question_text',
        'question_type',
        'difficulty',
        'points',
        'image_path',
        'explanation',
        'sequence_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'points' => 'integer',
        'sequence_number' => 'integer',
    ];

    /**
     * Get the QCM paper that owns the question.
     */
    public function qcmPaper(): BelongsTo
    {
        return $this->belongsTo(QcmPaper::class);
    }

    /**
     * Get the section that owns the question.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(QcmSection::class, 'section_id');
    }

    /**
     * Get the answers for the question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QcmAnswer::class);
    }
    
    /**
     * Get the correct answer for the question.
     */
    public function correctAnswer()
    {
        return $this->answers()->where('is_correct', true)->first();
    }
    
    /**
     * Get the exam answers for the question.
     */
    public function examAnswers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }
    
    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('answers', function ($q) {
            $q->where('status', true);
        });
    }
    
    /**
     * Get the image URL attribute.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        
        return null;
    }
    
    /**
     * Get the question type as text.
     */
    public function getQuestionTypeTextAttribute()
    {
        return [
            'multiple_choice' => 'Multiple Choice',
            'yes_no' => 'Yes/No',
        ][$this->question_type] ?? $this->question_type;
    }
    
    /**
     * Get the difficulty as text.
     */
    public function getDifficultyTextAttribute()
    {
        return [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard',
        ][$this->difficulty] ?? $this->difficulty;
    }
}

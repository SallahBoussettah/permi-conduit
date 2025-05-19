<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcmSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'qcm_paper_id',
        'title',
        'description',
        'sequence_number',
    ];

    /**
     * Get the QCM paper that owns the section.
     */
    public function qcmPaper(): BelongsTo
    {
        return $this->belongsTo(QcmPaper::class);
    }

    /**
     * Get the questions for the section.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QcmQuestion::class, 'section_id');
    }
    
    /**
     * Get the count of questions in this section
     */
    public function getQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }
    
    /**
     * Get the total points available in this section
     */
    public function getTotalPointsAttribute(): int
    {
        return $this->questions()->sum('points');
    }
} 
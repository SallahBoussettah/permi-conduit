<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_section_id',
        'description',
        'scoring_type',
        'reference_in_pdf',
    ];

    /**
     * Get the exam section that owns the exam item.
     */
    public function examSection(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class);
    }

    /**
     * Get the exam results for the exam item.
     */
    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}

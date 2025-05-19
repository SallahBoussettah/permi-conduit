<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcmPaper extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'permit_category_id',
        'created_by',
        'status',
        'school_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the permit category that owns the QCM paper.
     */
    public function permitCategory(): BelongsTo
    {
        return $this->belongsTo(PermitCategory::class);
    }

    /**
     * Get the user that created the QCM paper.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the questions for the QCM paper.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QcmQuestion::class);
    }

    /**
     * Get the sections for the QCM paper.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(QcmSection::class)->orderBy('sequence_number');
    }

    /**
     * Get the exams for the QCM paper.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(QcmExam::class);
    }

    /**
     * Get the school that owns the QCM paper.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope a query to only include active papers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}

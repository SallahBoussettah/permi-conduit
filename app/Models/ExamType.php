<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'pdf_reference',
        'description',
    ];

    /**
     * Get the exam sections for the exam type.
     */
    public function examSections(): HasMany
    {
        return $this->hasMany(ExamSection::class);
    }

    /**
     * Get the exams for the exam type.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}

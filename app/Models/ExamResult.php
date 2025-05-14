<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_id',
        'exam_item_id',
        'score_achieved',
        'notes_by_inspector',
    ];

    /**
     * Get the exam that owns the exam result.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the exam item that owns the exam result.
     */
    public function examItem(): BelongsTo
    {
        return $this->belongsTo(ExamItem::class);
    }
}

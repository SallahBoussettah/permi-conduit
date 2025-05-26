<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatConversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'candidate_id',
        'inspector_id',
        'status',
        'closed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'closed_at' => 'datetime',
    ];

    /**
     * Get the candidate that owns the conversation.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    /**
     * Get the inspector that may be assigned to the conversation.
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Get the messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /**
     * Get the last message of the conversation.
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latest();
    }

    /**
     * Scope a query to only include active conversations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'closed');
    }

    /**
     * Scope a query to only include conversations waiting for inspector.
     */
    public function scopeWaitingForInspector($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include conversations with an inspector.
     */
    public function scopeWithInspector($query)
    {
        return $query->where('status', 'inspector_joined');
    }

    /**
     * Scope a query to only include closed conversations.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    use HasFactory;

    // Notification types
    const TYPE_COURSE = 'course';
    const TYPE_EXAM = 'exam';
    const TYPE_SYSTEM = 'system';
    const TYPE_REMINDER = 'reminder';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'message',
        'type',
        'read_at',
        'link',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead(Builder $query): void
    {
        $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeInDateRange(Builder $query, $startDate, $endDate): void
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
    }

    /**
     * Get icon class based on notification type
     */
    public function getIconClass(): string
    {
        return match($this->type) {
            self::TYPE_COURSE => 'fa-book',
            self::TYPE_EXAM => 'fa-clipboard-check',
            self::TYPE_REMINDER => 'fa-bell',
            default => 'fa-info-circle',
        };
    }

    /**
     * Get color class based on notification type
     */
    public function getColorClass(): string
    {
        return match($this->type) {
            self::TYPE_COURSE => 'bg-blue-50 border-blue-200',
            self::TYPE_EXAM => 'bg-green-50 border-green-200',
            self::TYPE_REMINDER => 'bg-yellow-50 border-yellow-200',
            default => 'bg-gray-50 border-gray-200',
        };
    }

    /**
     * Get color text class based on notification type
     */
    public function getTextColorClass(): string
    {
        return match($this->type) {
            self::TYPE_COURSE => 'text-blue-800',
            self::TYPE_EXAM => 'text-green-800',
            self::TYPE_REMINDER => 'text-yellow-800',
            default => 'text-gray-800',
        };
    }
}

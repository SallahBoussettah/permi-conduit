<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermitCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
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
     * Get the courses for this permit category.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'permit_category_id');
    }

    /**
     * Get the users with this permit category.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'permit_category_id');
    }

    /**
     * Scope a query to only include active permit categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get the status text attribute.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return $this->status ? 'Active' : 'Inactive';
    }
} 
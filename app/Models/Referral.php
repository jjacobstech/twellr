<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'status',
        'reward_points',
        'code_used',
        'converted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'converted_at' => 'datetime',
    ];

    /**
     * Get the user who referred (the referrer).
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    /**
     * Scope a query to only include converted referrals.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    /**
     * Scope a query to only include pending referrals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
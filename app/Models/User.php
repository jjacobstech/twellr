<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use App\Models\Notification;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    /**
     * Class User
     *
     * @property int $id
     * @property string $firstname
     * @property string $lastname
     * @property string|null $avatar
     * @property string|null $cover
     * @property string|null $address
     * @property string|null $phone_no
     * @property string $email
     * @property string|null $google_id
     * @property string $role
     * @property Carbon|null $email_verified_at
     * @property string $password
     * @property float|null $price
     * @property float|null $wallet_balance
     * @property string|null $bank_name
     * @property string|null $account_name
     * @property string|null $account_no
     * @property string $notify_purchase
     * @property string|null $remember_token
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     *
     * @package App\Models
     */

    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'rating' => 'int',
        'wallet_balance' => 'float'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'firstname',
        'lastname',
        'avatar',
        'cover',
        'address',
        'phone_no',
        'email',
        'google_id',
        'role',
        'email_verified_at',
        'facebook',
        'x',
        'instagram',
        'whatsapp',
        'password',
        'rating',
        'referral_link',
        'wallet_balance',
        'bank_name',
        'account_name',
        'account_no',
        'notify_purchase',
        'remember_token',
        'referred_by',
        'discount'
    ];

    public function isCreative()
    {
        if (Auth::user()->role == 'creative') {
            return true;
        }
        return false;
    }

    public function isAdmin()
    {
        if (Auth::user()->role == 'admin') {
            return true;
        }
        return false;
    }


    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Get all users who follow this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Check if user is following another user
     *
     * @param int $userId
     * @return bool
     */
    public function isFollowing($userId)
    {
        return $this->following()->where('following_id', $userId)->exists();
    }

    public function pickedForYou()
    {
        // Get IDs of users this user is following
        $followingIds = $this->following()->pluck('users.id');

        // If not following anyone, return empty collection
        if ($followingIds->isEmpty()) {
            return collect();
        }

        // Return products where the user_id is in the following list
        return Product::whereIn('user_id', $followingIds)
            ->latest()->take(7)  // Order by most recent first
            ->get();
    }

    /**
     * Check if user is followed by another user
     *
     * @param int $userId
     * @return bool
     */
    public function isFollowedBy($userId)
    {
        return $this->followers()->where('user_id', $userId)->exists();
    }

    /**
     * Get count of followers
     *
     * @return int
     */
    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    /**
     * Get count of users being followed
     *
     * @return int
     */
    public function getFollowingCountAttribute()
    {
        return $this->following()->count();
    }

}

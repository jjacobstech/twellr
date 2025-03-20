<?php

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
        'price' => 'float',
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
        'password',
        'price',
        'wallet_balance',
        'referral_link',
        'rating',
        'bank_name',
        'account_name',
        'account_no',
        'notify_purchase',
        'remember_token'
    ];

    public function isCreative()
    {
        if (Auth::user()->role == 'creative') {
            return true;
        }
        return false;
    }

    public function notifications()
    {
        return  $this->hasMany(Notification::class, 'user_id');
    }
}

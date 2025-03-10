<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * 
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string|null $avatar
 * @property string|null $cover
 * @property string|null $address
 * @property string $phone_no
 * @property string $email
 * @property string|null $google_id
 * @property string $role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property float $price
 * @property float $wallet_balance
 * @property string $bank_name
 * @property string $account_name
 * @property string $account_no
 * @property string $notify_purchase
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class User extends Model
{
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime',
		'price' => 'float',
		'wallet_balance' => 'float'
	];
}

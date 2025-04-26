<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Follower
 * 
 * @property int $id
 * @property int $user_id
 * @property int $following_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models\Base
 */
class Follower extends Model
{
	protected $table = 'followers';

	protected $casts = [
		'user_id' => 'int',
		'following_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'following_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}

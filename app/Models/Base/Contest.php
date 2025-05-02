<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Contest
 * 
 * @property int $id
 * @property int|null $user_id
 * @property int|null $product_id
 * @property int|null $rating_id
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class Contest extends Model
{
	protected $table = 'contests';

	protected $casts = [
		'user_id' => 'int',
		'product_id' => 'int',
		'rating_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'product_id',
		'rating_id',
		'type'
	];
}

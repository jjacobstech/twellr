<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property float $price
 * @property int $delivery_time
 * @property string $description
 * @property string $designfile
 * @property string $designimage
 * @property string|null $mime
 * @property string|null $extension
 * @property string|null $size
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';

	protected $casts = [
		'user_id' => 'int',
		'price' => 'float',
		'delivery_time' => 'int'
	];

	protected $fillable = [
		'user_id',
		'name',
		'type',
		'price',
		'delivery_time',
		'description',
		'designfile',
		'designimage',
		'mime',
		'extension',
		'size',
		'status'
	];
}

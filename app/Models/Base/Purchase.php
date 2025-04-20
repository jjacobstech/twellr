<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Purchase
 * 
 * @property int $id
 * @property int $transactions_id
 * @property int $user_id
 * @property int $buyer_id
 * @property int $product_id
 * @property string $delivery_status
 * @property string $phone_no
 * @property string $address
 * @property float $amount
 * @property string|null $location
 * @property string|null $size
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $expired_at
 * @property string $product_name
 * @property string $product_category
 * @property string $material
 *
 * @package App\Models\Base
 */
class Purchase extends Model
{
	protected $table = 'purchases';

	protected $casts = [
		'transactions_id' => 'int',
		'user_id' => 'int',
		'buyer_id' => 'int',
		'product_id' => 'int',
		'amount' => 'float',
		'expired_at' => 'datetime'
	];

	protected $fillable = [
		'transactions_id',
		'user_id',
		'buyer_id',
		'product_id',
		'delivery_status',
		'phone_no',
		'address',
		'amount',
		'location',
		'size',
		'expired_at',
		'product_name',
		'product_category',
		'material'
	];
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Purchase
 *
 * @property int $id
 * @property int $transactions_id
 * @property int $user_id
 * @property int $products_id
 * @property string $delivery_status
 * @property string|null $description_custom_content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $address
 * @property string|null $city
 * @property string|null $zip
 * @property string|null $phone
 * @property Carbon|null $expired_at
 *
 * @package App\Models
 */
class Purchase extends Model
{
    protected $table = 'purchases';

    protected $casts = [
        'transactions_id' => 'int',
        'user_id' => 'int',
        'products_id' => 'int',
        'expired_at' => 'datetime'
    ];

    protected $fillable = [
        'transactions_id',
        'user_id',
        'products_id',
        'delivery_status',
        'description_custom_content',
        'address',
        'city',
        'zip',
        'phone',
        'expired_at'
    ];
}
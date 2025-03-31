<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{


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
     * @property string $city
     * @property string $zip
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @property Carbon|null $expired_at
     *
     * @package App\Models\Base
     */
    protected $table = 'purchases';

    protected $casts = [
        'transactions_id' => 'int',
        'user_id' => 'int',
        'buyer_id' => 'int',
        'product_id' => 'int',
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
        'city',
        'zip',
        'expired_at'
    ];
}
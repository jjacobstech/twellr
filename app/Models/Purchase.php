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
      'location_id',
        'size',
        'product_name',
        'product_category',
        'material_id',
        'quantity',
        'discounted'

    ];

    public function customer(){
        return $this->belongsTo(User::class, 'buyer_id','id');
    }


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }


    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'id');
    }
    public function shippingLocation()
    {
        return $this->belongsTo(ShippingFee::class, 'location_id', 'id');
    }


}

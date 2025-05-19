<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'transaction_id',
        'purchase_id',
        'amount',
        'ref_no'
    ];
}

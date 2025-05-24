<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $casts = [
        'user_id' => 'int',
        'product_id' => 'int'
    ];

    protected $fillable = [
        'user_id',
        'product_id',
        'review'
    ];
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
        'category_id',
        'rating_id',
        'name',
        'description',
        'photo',
        'type'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function frontView(){
        return $this->hasOne(Product::class, 'id', 'product_id')->select('id', 'front_view', 'front_view_mime', 'front_view_extension', 'front_view_size');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}

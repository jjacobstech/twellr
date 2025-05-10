<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Vote
 *
 * @property int $id
 * @property int $user_id
 * @property int $contestant_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 * @property Product $product
 *
 * @package App\Models\Base
 */
class Vote extends Model
{
    protected $table = 'votes';

    protected $casts = [
        'user_id' => 'int',
        'contestant_id' => 'int',
        'product_id' => 'int'
    ];

    protected $fillable = [
        'user_id',
        'contestant_id',
        'product_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

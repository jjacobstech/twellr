<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ContestWinner
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
class ContestWinner extends Model
{
    protected $table = 'contest_winners';

    protected $casts = [
        'user_id' => 'int',
        'product_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'product_id',
        'contest_type',
        'votes',
        'won_at'
    ];

    public static function  weekly()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return ContestWinner::whereBetween('created_at', [$startOfWeek, $endOfWeek]);
    }
    public function monthly(Builder $query): Builder
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public static function winner()
    {
        $winner = self::where('contest_type', 'design_fest')->first();

        if ($winner) {
            return Product::where('user_id', $winner->user_id)->get();
        } else {
            return;
        }
    }
}

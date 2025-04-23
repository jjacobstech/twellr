<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string $transaction_type
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Transaction extends Model
{
    protected $table = 'transactions';

    protected $casts = [
        'user_id' => 'int',
        'amount' => 'float',
        'created_at' => 'date:d-m-Y'
    ];

    protected $fillable = [
        'user_id',
        "buyer_id",
        'amount',
        'transaction_type',
        'status',
        'ref_no'
    ];
}

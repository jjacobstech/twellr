<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Deposit
 *
 * @property int $id
 * @property int $user_id
 * @property int $transaction_id
 * @property string $ref_no
 * @property int $amount
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class Deposit extends Model
{
    protected $table = 'deposits';

    protected $casts = [
        'user_id' => 'int',
        'transaction_id' => 'int',
        'amount' => 'int'
    ];

    protected $fillable = [
        'user_id',
        'transaction_id',
        'ref_no',
        'amount',
        'status'
    ];
}

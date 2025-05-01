<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformEarning extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'purchase_id',
        'transaction_id',
        'total',
        'quantity',
        'price',
        'fee_type',
        'notes'
    ];

   /**
     * Calculate the total earnings for this record.
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        return $this->shipping_fee +
            $this->platform_commission +
            $this->tax_collected +
            $this->additional_fees;
    }

    /**
     * Scope a query to only include earnings within a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}

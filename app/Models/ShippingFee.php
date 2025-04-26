<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'rate',
        'active'
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'active' => 'boolean'
    ];

    /**
     * Scope a query to only include active shipping rates.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Find shipping rate for a specific location.
     *
     * @param string $location
     * @return \App\Models\ShippingRate|null
     */
    public static function findByLocation($location)
    {
        return self::active()->where('location', $location)->first();
    }

    /**
     * Get all available shipping locations.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAvailableLocations()
    {
        return self::active()->pluck('location');
    }
}

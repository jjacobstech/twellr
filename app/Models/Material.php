<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Material
 *
 * @property int $d
 * @property string $name
 * @property string $quality
 * @property float $price
 * @property string $description
 * @property string|null $image
 * @property string $availability
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 */
class Material extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'id';

    protected $casts = [
        'price' => 'float'
    ];

    protected $fillable = [
        'name',
        'quality',
        'price',
        'description',
        'image',
        'availability'
    ];

}

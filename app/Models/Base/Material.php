<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

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
 * @package App\Models\Base
 */
class Material extends Model
{
	protected $table = 'materials';
	protected $primaryKey = 'd';

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

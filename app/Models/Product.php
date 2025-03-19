<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property float $price
 * @property string $category
 * @property string $description
 * @property string $print_stack
 * @property string|null $print_stack_mime
 * @property string|null $print_stack_extension
 * @property string|null $print_stack_size
 * @property string $design_stack
 * @property string|null $design_stack_mime
 * @property string|null $design_stack_extension
 * @property string|null $size
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Product extends Model
{
    protected $table = 'products';

    protected $casts = [
        'user_id' => 'int',
        'price' => 'float'
    ];

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'price',
        'category',
        'description',
        'print_stack',
        'print_stack_mime',
        'print_stack_extension',
        'print_stack_size',
        'design_stack',
        'design_stack_mime',
        'design_stack_extension',
        'design_stack_size',
        'status'
    ];
}

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
        'front_view',
        'front_view_mime',
        'front_view_extension',
        'front_view_size',
        'back_view',
        'back_view_mime',
        'back_view_extension',
        'back_view_size',
        'side_view',
        'side_view_mime',
        'side_view_extension',
        'side_view_size',
        'description',
        'print_stack',
        'print_stack_mime',
        'print_stack_extension',
        'print_stack_size',
        'status'
    ];
}

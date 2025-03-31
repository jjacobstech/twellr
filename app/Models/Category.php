<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{



    /**
     * Class Category
     *
     * @property int $id
     * @property string $name
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     *
     * @package App\Models\Base
     */

    protected $table = 'categories';

    protected $fillable = [
        'name'
    ];
}

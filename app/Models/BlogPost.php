<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BlogPost
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $image
 * @property int $category
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BlogCategory $blog_category
 *
 */
class BlogPost extends Model
{
    protected $table = 'blog_posts';

    protected $casts = [
        'category' => 'int'
    ];

    protected $fillable = [
        'title',
        'content',
        'image',
        'category_id'
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id', 'id');
    }

}

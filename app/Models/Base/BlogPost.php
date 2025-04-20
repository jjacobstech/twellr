<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BlogCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BlogPost
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $category
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BlogCategory $blog_category
 *
 * @package App\Models\Base
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
		'category'
	];

	public function blog_category()
	{
		return $this->belongsTo(BlogCategory::class, 'category','id');
	}
}

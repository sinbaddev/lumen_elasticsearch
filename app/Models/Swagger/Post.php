<?php

namespace App\Models\Swagger;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;
/**
 * @SWG\Definition(definition="Post",required={"title", "content"}, type="object", @SWG\Xml(name="Post"))
 * @SWG\Property(property="id", type="integer", description="Post id"),
 * @SWG\Property(property="title", type="string", description="Post title"),
 * @SWG\Property(property="slug", type="string", description="Post slug"),
 * @SWG\Property(property="content", type="string", description="Post content"),
 * @SWG\Property(property="created_at", type="string", description="Post created date", format="date-time"),
 * @SWG\Property(property="updated_at", type="string", description="Post updated date", format="date-time"),
 */
class Post extends Model
{
    protected $table = 'posts';

    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'slug', 'content'
    ];

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function getList($input)
    {
        $limit = $input['limit'] ?? 15;
        $posts = Post::paginate($limit);
        
        return $posts;
    }

    public function getDetail($id)
    {
        $post = Post::where('id', $id)->first();  

        return $post;
    }

    public function store($input)
    {
        $input['slug'] = Str::slug($input['title'], '-');
        $post = Post::create($input);  

        return $post;
    }

    public function updatePost($id, $input)
    {
        $update = Post::where('id', $id)->update($input);
        $post = $this->getDetail($id);

        return $post;
    }

    public function deletePost($id)
    {
        $delete = Post::where('id', $id)->delete();

        return $delete;
    }
}
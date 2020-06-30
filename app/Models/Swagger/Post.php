<?php

namespace App\Models\Swagger;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'title', 'slug', 'content', 'status'
    ];

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function getList()
    {
        $posts = Post::paginate(15);
        
        return $posts;
    }

    public function getDetail($id)
    {
        $post = Post::findOrFail($id);  

        return $post;
    }

    public function store($input)
    {
        $post = Post::create($input);  

        return $post;
    }

    public function updatePost($id, $input)
    {
        $post = Post::findOrFail($id);
        $post->fill($input);

        if ($post->save()) {
            return $post;
        }
        return false;
    }
}
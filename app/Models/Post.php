<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    const ELASTIC_INDEX = 'post_index';
    const ELASTIC_TYPE = 'post_type';

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

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function getList()
    {
        $posts = Post::limit(5000)->get();
        
        return $posts;
    }

    public function getDetail($id)
    {
        $post = Post::findOrFail($id);  

        return $post;
    }

    public function getDataCreateIndexListPost($input = [])
    {
        $posts = $this->getList();

        $data = [];

        foreach ($posts as $key => $post) {
            $data['body'][] = [
                'index' => [
                    '_index' => self::ELASTIC_INDEX,
                    '_type' => self::ELASTIC_TYPE,
                    '_id' => $post->id,
                ]
            ];

            $data['body'][] = [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at
            ];
        }

        return $data;
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
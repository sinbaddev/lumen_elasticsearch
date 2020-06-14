<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
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
        $posts = Post::limit(15000)->offset(15018)->get();
        
        return $posts;
    }

    public function getDetail($id)
    {
        $post = Post::findOrFail($id);  

        return $post;
    }

    public function getDataCreateListDocumentPost($input = [])
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

            $data['body'][] = $this->transformData($post);
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

    public function transformData($post)
    {
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'author_id' => $post->author_id,
            'author_name' => object_get($post, 'author.name', ''),
            'status' => $post->status,
            'status_name' => $post->status == 1 ? 'Active' : 'Inactive',
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
        ];

        return $data;
    }

    public function getParamIndex($is_type = 0, $id = null, $is_search = 0, $input = [])
    {
        $params = [
            'index' => self::ELASTIC_INDEX
        ];

        if ($is_type == 1) {
            $params['type'] = self::ELASTIC_TYPE;
        }

        if ($id) {
            $params['id'] = $id;
        }

        if ($is_search == 1) {
            $limit = $input['limit'] ?? 20;
            $page = $input['page'] ?? 1;
            $sort = $input['sort'] ?? ['id' => 'asc'];

            $params['body'] = [
                'size' => $limit, // limit
                'from' => $limit * ($page - 1),
                "sort" => $sort,
            ];
        }

        if (!empty($input) && (count($input) > 0)) {
            $is_query = 0;
            if (!empty($input['created_at'])) {
                $is_query = 1;
                $query['range']['created_at'] = ['gte' => $input['created_at'], 'format' => 'yyyy-MM-dd'];
            }
    
            if (!empty($input['updated_at'])) {
                $is_query = 1;
                $query['range']['updated_at'] = ['gte' => $input['updated_at'], 'format' => 'yyyy-MM-dd'];
            }

            if (!empty($input['title'])) {
                $is_query = 1;
                $query['match']['title'] = $input['title'];
            }
    
            if (!empty($input['name'])) {
                $is_query = 1;
                $query['match']['name'] = $input['name'];
            }
    
            if ($is_query == 1) {
                $params['body']['query'] = $query;
            }
        }

        return $params;
    }

    public function getDataIndex($post, $is_update = 0)
    {
        $data = [
            'index' => self::ELASTIC_INDEX,
            'type' => self::ELASTIC_TYPE,
            'id' => $post->id,
            'body' => $this->transformData($post),
        ];

        if ($is_update == 1) {
            $data['body'] = [
                'doc' => $this->transformData($post),
            ];
        }

        return $data;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Author extends Model
{
    const ELASTIC_INDEX = 'author_index';
    const ELASTIC_TYPE = 'author_type';

    protected $table = 'authors';

    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getList()
    {
        $authors = Author::all();
        
        return $authors;
    }

    public function getDetail($id)
    {
        $author = Author::findOrFail($id);  

        return $author;
    }

    public function getDataCreateIndexListAuthor($input = [])
    {
        $authors = $this->getList();

        $data = [];

        foreach ($authors as $key => $author) {
            $data['body'][] = [
                'index' => [
                    '_index' => self::ELASTIC_INDEX,
                    '_type' => self::ELASTIC_TYPE,
                    '_id' => $author->id,
                ]
            ];

            $data['body'][] = $this->transformData($author);
        }

        return $data;
    }

    public function store($input)
    {
        $author = Author::create($input);  
        return $author;
    }

    public function updateAuthor($id, $input)
    {
        $author = Author::findOrFail($id);
        $author->fill($input);

        if ($author->save()) {
            return $author;
        }
        return false;
    }

    public function transformData($author)
    {
        $data = [
            'id' => $author->id,
            'name' => $author->name,
            'created_at' => $author->created_at,
            'updated_at' => $author->updated_at,
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

        if (!empty($input) && (count($input) > 0) && $is_search == 1) {
            $limit = $input['limit'] ?? 20;
            $page = $input['page'] ?? 1;
            $sort = $input['sort'] ?? ['id' => 'asc'];
            $is_query = 0;

            $params['body'] = [
                'size' => $limit, // limit
                'from' => $limit * ($page - 1),
                "sort" => $sort,
            ];

            if (!empty($input['created_at'])) {
                $is_query = 1;
                $query['range']['created_at'] = ['gte' => $input['created_at'], 'format' => 'yyyy-MM-dd'];
            }
    
            if (!empty($input['updated_at'])) {
                $is_query = 1;
                $query['range']['updated_at'] = ['gte' => $input['updated_at'], 'format' => 'yyyy-MM-dd'];
            }
    
            if (!empty($input['name'])) {
                $is_query = 1;
                $query['match'] = ['name' => $input['name']];
            }
    
            if ($is_query == 1) {
                $params['body']['query'] = $query;
            }
        }

        return $params;
    }

    public function getDataIndex($author, $is_doc = 0)
    {
        $data = [
            'index' => self::ELASTIC_INDEX,
            'type' => self::ELASTIC_TYPE,
            'id' => $author->id,
            'body' => $this->transformData($author),
        ];

        if ($is_doc == 1) {
            $data['body'] = [
                'doc' => $this->transformData($author),
            ];
        }

        return $data;
    }
}
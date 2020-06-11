<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use Elasticsearch\ClientBuilder;

class PostController extends Controller
{
    protected $request;
    protected $postModel;
    protected $elasticsearch;

    public function __construct(
        Request $request,
        Post $postModel
    ) {
        $this->request = $request;
        $this->postModel = $postModel;
        $this->elasticsearch = ClientBuilder::create()->build();
    }
    
    public function createIndex()
    {
        $data = $this->postModel->getDataCreateIndexListPost();
        $response = $this->elasticsearch->bulk($data);

        return response()->json([
            "data" => [
                'Create Index Successful'
            ]
        ], Response::HTTP_OK); 
    }

    public function createDetailIndex($id)
    {
        $post = $this->postModel->getDetail($id);
    
        $this->_processCreateNewIndex($post);

        return response()->json([
            "data" => [
                $post
            ]
        ], Response::HTTP_OK); 
    }

    public function index()
    {
//         $da = $this->elasticsearch->cluster()->stats();
// print_r($da);die;
        $input = $this->request->all();
        $query = [];

        if (!empty($input['title'])) {
            $query = [
                'match' => [
                    "title" => $input['title']
                ],
            ];
        }

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => [
                'query' => $query
                //"_source" => [ "title", "slug", "content" ], // show field
                //'size' => 1, // limit
             ]
        ];
        
        try {
            $items = $this->elasticsearch->search($params);

            //$hits = array_pluck($items['hits']['hits'], '_source') ?: [];

            return response()->json([
                "data" => [
                    $items
                    ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function detail($id)
    {
        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'id'    => $id
        ];

        try {
            $post = $this->postModel->getDetail($id);
            try {
                $get_doc = $this->elasticsearch->get($params);
            } catch (ElasticsearchException $e) {
            }
            //$get_source = $this->elasticsearch->getSource($params);

            return response()->json([
                "data" => [
                    $get_doc
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function store()
    {
        $input = $this->request->all();

        $post = $this->postModel->store($input);

        $this->_processCreateNewIndex($post);

        return response()->json([
            "data" => [
                $post
            ]
        ], Response::HTTP_OK); 
    }

    public function update($id)
    {
        // update data post
        $input = $this->request->all();
        $post = $this->postModel->updatePost($id, $input);

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'id' => $id,
            'body' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at
            ]
        ];

        $response = $this->elasticsearch->update($params);
        // delete current index
        // $this->_processDeleteDocument($id);
        // create new index
        // $this->_processCreateNewIndex($post);

        return response()->json([
            "data" => [
                $post
            ]
        ], Response::HTTP_OK); 
    }

    public function deleteIndex()
    {
        try {
            $response = $this->elasticsearch->indices()->delete(['index' => Post::ELASTIC_INDEX]);

            return response()->json([
                "data" => [
                    $response
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function deleteDetailIndex($id)
    {
        try {
            $response = $this->_processDeleteDocument($id);

            return response()->json([
                "data" => [
                    $response
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], $e->getCode());
        }
    }

    private function _processCreateNewIndex($model)
    {
        $data = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'id' => $model->id,
            'body' => [
                'id' => $model->id,
                'title' => $model->title,
                'slug' => $model->slug,
                'content' => $model->content,
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at
            ]
        ];

        $response = $this->elasticsearch->index($data);

        return $response;
    }

    private function _processDeleteDocument($id)
    {
        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'id' => $id
        ];

        $response = $this->elasticsearch->delete($params);

        return $response;
    }
}
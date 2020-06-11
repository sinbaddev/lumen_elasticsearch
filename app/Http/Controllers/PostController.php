<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
                'Create Index Successful',
            ],
        ], Response::HTTP_OK);
    }

    public function createDetailIndex($id)
    {
        $post = $this->postModel->getDetail($id);

        $this->_processCreateNewIndex($post);

        return response()->json([
            "data" => [
                $post,
            ],
        ], Response::HTTP_OK);
    }

    public function index()
    {
        $input = $this->request->all();

        $limit = $input['limit'] ?? 20;
        $page = $input['page'] ?? 1;
        $sort = $input['sort'] ?? ['id' => 'asc'];

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => [
                //"_source" => [ "title", "slug", "content" ], // show field
                'size' => $limit, // limit
                'from' => $limit * ($page - 1),
                "sort" => $sort,
            ],
        ];

        if (!empty($input['title'])) {
            $params['body']['query'] = [
                'match' => [
                    "title" => $input['title'],
                ],
            ];
        }

        try {
            $items = $this->elasticsearch->search($params);

            $hits = array_pluck($items['hits']['hits'], '_source') ?: [];

            return response()->json([
                "data" => $hits,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function detail($id)
    {
        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'id' => $id,
        ];

        try {
            $post = $this->postModel->getDetail($id);

            $isExistDoc = $this->elasticsearch->exists($params);
            if ($isExistDoc) {
                $get_doc = $this->elasticsearch->getSource($params);
            } else {
                $data = [
                    'index' => Post::ELASTIC_INDEX,
                    'type' => Post::ELASTIC_TYPE,
                    'id' => $post->id,
                    'body' => [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'content' => $post->content,
                        'created_at' => $post->created_at,
                        'updated_at' => $post->updated_at,
                    ],
                ];

                $create_doc = $this->elasticsearch->index($data);

                $this->elasticsearch->indices()->refresh();

                $get_doc = $this->elasticsearch->getSource($params);
            }

            //$get = $this->elasticsearch->get($params);

            return response()->json([
                "data" => [
                    $get_doc,
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function store()
    {
        $input = $this->request->all();

        $post = $this->postModel->store($input);

        $data = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'id' => $post->id,
            'body' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ],
        ];

        $response = $this->elasticsearch->index($data);

        return response()->json([
            "data" => [
                $post,
            ],
        ], Response::HTTP_OK);
    }

    public function update($id)
    {
        // update data post
        $input = $this->request->all();
        $post = $this->postModel->updatePost($id, $input);

        $params_exist = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'id' => $id,
        ];
        $isExistDoc = $this->elasticsearch->exists($params_exist);
        if ($isExistDoc) {
            $params = [
                'index' => Post::ELASTIC_INDEX,
                'type' => Post::ELASTIC_TYPE,
                'id' => $id,
                'body' => [
                    'doc' => [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'content' => $post->content,
                        'created_at' => $post->created_at,
                        'updated_at' => $post->updated_at,
                    ],
                ],
            ];

            $response = $this->elasticsearch->update($params);
        } else {
            $data = [
                'index' => Post::ELASTIC_INDEX,
                'type' => Post::ELASTIC_TYPE,
                'id' => $post->id,
                'body' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'content' => $post->content,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                ],
            ];

            $response = $this->elasticsearch->index($data);
        }

        return response()->json([
            "data" => [
                $post,
            ],
        ], Response::HTTP_OK);
    }

    public function deleteIndex()
    {
        try {
            $response = $this->elasticsearch->indices()->delete(['index' => Post::ELASTIC_INDEX]);

            return response()->json([
                "data" => [
                    $response,
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function deleteDetailIndex($id)
    {
        try {
            $params = [
                'index' => Post::ELASTIC_INDEX,
                'type' => Post::ELASTIC_TYPE,
                'id' => $id,
            ];

            $response = $this->elasticsearch->delete($params);

            return response()->json([
                "data" => [
                    $response,
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    private function _processDeleteDocument($id)
    {

        return $response;
    }
}

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
    private $_msgErrorNotExist = 'The index does not exist.';
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
        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => []
        ];

        try {
            $response = $this->elasticsearch->index($params);

            return response()->json([
                "data" => [$response],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function deleteIndex()
    {
        $params = $this->postModel->getParamIndex();

        try {
            $isExistIndex = $this->elasticsearch->indices()->exists($params);
            if (!$isExistIndex) {
                return response()->json([
                    "error" => $this->_msgErrorNotExist,
                ], 404);
            }

            $response = $this->elasticsearch->indices()->delete($params);

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

    public function searchIndex()
    {
        $params = [
            'index' => Post::ELASTIC_INDEX
        ];

        try {
            $isExistIndex = $this->elasticsearch->indices()->exists($params);
            if (!$isExistIndex) {
                return response()->json([
                    "error" => $this->_msgErrorNotExist,
                ], 404);
            }

            $result = $this->elasticsearch->indices()->get($params);

            return response()->json([
                "data" => [
                    $result,
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function createListDocument()
    {
        $data = $this->postModel->getDataCreateListDocumentPost();
        $response = $this->elasticsearch->bulk($data);

        return response()->json([
            "data" => [
                'Create Index Successful',
            ],
        ], Response::HTTP_OK);
    }

    public function searchDocumentPagination()
    {
        $input = $this->request->all();
        $limit = $input['limit'] ?? 15;
        $page = $input['page'] ?? 1;
        $sort = $input['sort'] ?? ['id' => 'asc'];

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => [
                'size' => $limit,
                'from' => $limit * ($page - 1),
                "sort" => $sort,
            ],
        ];

        try {
            $items = $this->elasticsearch->search($params);

            //$hits = array_pluck($items['hits']['hits'], '_source') ?: [];

            return response()->json([
                "data" => [$items],
                //"data" => $hits,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function createDocument()
    {
        $input = $this->request->all();

        $post = $this->postModel->store($input);

        $data = $this->postModel->getDataIndex($post);

        $response = $this->elasticsearch->index($data);

        return response()->json([
            "data" => [
                $post,
            ],
        ], Response::HTTP_OK);
    }

    public function searchDocumentMatch()
    {
        $input = $this->request->all();

        $isExistIndex = $this->elasticsearch->indices()->exists(['index' => Post::ELASTIC_INDEX]);
        if (!$isExistIndex) {
            return response()->json([
                "error" => $this->_msgErrorNotExist,
            ], 404);
        }

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => [
                'query' => [
                    // 'query_string' => [
                    //     'query' => $input['title'],
                    //     'default_field' => 'title'
                    // ]
                    'match' => ['title' => $input['title']]
                ]
            ]
        ];

        try {
            $items = $this->elasticsearch->search($params);

            //$hits = array_pluck($items['hits']['hits'], '_source') ?: [];

            return response()->json([
                "data" => [$items],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function searchAndShowFieldExpect()
    {
        $input = $this->request->all();

        $isExistIndex = $this->elasticsearch->indices()->exists(['index' => Post::ELASTIC_INDEX]);
        if (!$isExistIndex) {
            return response()->json([
                "error" => $this->_msgErrorNotExist,
            ], 404);
        }

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => [
                '_source' => ['id', 'title', 'slug', 'content', 'author_name']
            ]
        ];

        try {
            $items = $this->elasticsearch->search($params);

            return response()->json([
                "data" => [$items],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function index()
    {
        $input = $this->request->all();

        $isExistIndex = $this->elasticsearch->indices()->exists($this->postModel->getParamIndex());
        if (!$isExistIndex) {
            return response()->json([
                "error" => $this->_msgErrorNotExist,
            ], 404);
        }

        $params = $this->postModel->getParamIndex(1, null, 1, $input);
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

    public function updateDocument($id)
    {
        // update data post
        $input = $this->request->all();
        $post = $this->postModel->updatePost($id, $input);

        $params_exist = $this->postModel->getParamIndex(1, $id, 0, []);

        $isExistDoc = $this->elasticsearch->exists($params_exist);
        if ($isExistDoc) {
            $data = $this->postModel->getDataIndex($post, 1);

            $response = $this->elasticsearch->update($data);
        } else {
            $data = $this->postModel->getDataIndex($post);

            $response = $this->elasticsearch->index($data);
        }

        return response()->json([
            "data" => [
                $post,
            ],
        ], Response::HTTP_OK);
    }

    public function deleteDocument($id)
    {
        try {
            $params = $this->postModel->getParamIndex(1, $id, 0, []);

            $isExistDoc = $this->elasticsearch->exists($params);
            if (!$isExistDoc) {
                return response()->json([
                    "error" => $this->_msgErrorNotExist,
                ], 404);
            }

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

    public function detailDocument($id)
    {
        $params = $this->postModel->getParamIndex(1, $id, 0, []);

        try {
            $post = $this->postModel->getDetail($id);

            $isExistDoc = $this->elasticsearch->exists($params);
            if ($isExistDoc) {
                $get_doc = $this->elasticsearch->get($params);
            } else {
                $data = $this->postModel->getDataIndex($post);

                $create_doc = $this->elasticsearch->index($data);

                $this->elasticsearch->indices()->refresh();

                $get_doc = $this->elasticsearch->get($params);
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
}

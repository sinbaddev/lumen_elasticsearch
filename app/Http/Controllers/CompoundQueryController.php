<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompoundQueryController extends Controller
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

    public function searchBool()
    {
        $input = $this->request->all();
        $title = $input['title'] ?? '';
        $status = $input['status'] ?? 1;
        $sort = ['id' => 'asc'];

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => [
                'sort' => $sort,
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                        'boost' => 2.0,
                        'must' => [
                            'match' => ['title' => $title],
                        ], // condition and
                        'must_not' => [
                            'range' => [
                                'created_at' => ['gte' => '2020-06-06', 'lte' => '2020-06-09'],
                            ],
                        ], // condition not
                        'should' => [
                            'term' => ['status' => $status],
                        ], // condition or
                    ],
                ],
            ],
        ];

        try {
            $result = $this->elasticsearch->search($params);

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

    public function searchBoosting()
    {
        $input = $this->request->all();
        $title = $input['title'] ?? '';
        $status = $input['status'] ?? 1;
        $sort = ['id' => 'asc'];

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => [
                'sort' => $sort,
                'query' => [
                    'boosting' => [
                        'positive' => [
                            'match' => ['title' => $title],
                        ],
                        'negative' => [
                            'term' => ['status' => $status],
                        ],
                        'negative_boost' => 0.5,
                    ],
                ],
            ],
        ];

        try {
            $result = $this->elasticsearch->search($params);

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

    public function searchConstantScore()
    {
        $input = $this->request->all();
        $title = $input['title'] ?? '';
        $status = $input['status'] ?? 1;
        $sort = ['id' => 'asc'];

        $params = [
            'index' => Post::ELASTIC_INDEX,
            'type' => Post::ELASTIC_TYPE,
            'body' => [
                'sort' => $sort,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'term' => ['status' => $status],
                        ],
                        'boost' => 1.2,
                    ],
                ],
            ],
        ];

        try {
            $result = $this->elasticsearch->search($params);

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
}

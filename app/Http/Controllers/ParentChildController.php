<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ParentChildController extends Controller
{
    protected $request;
    protected $elasticsearch;
    private $_msgErrorNotExist = 'The index does not exist.';
    private $_my_index = 'artist_song_index';
    private $_my_type = 'artist_song_type';
    private $_my_id = 'artist_song_id';

    public function __construct(
        Request $request
    ) {
        $this->request = $request;
        $this->elasticsearch = ClientBuilder::create()->build();
    }

    public function createIndex()
    {
        $params = [
            'index' => $this->_my_index,
            'type' => $this->_my_type,
            'body' => [],
            // 'body' => [
            //     'mappings' => [
            //         $this->_my_type => [
            //             'properties' => [
            //                 "my_id" => [
            //                     "type" => "keyword"
            //                 ],
            //                 'relation_type' => [
            //                     'type' => 'join',
            //                     "eager_global_ordinals" => true,
            //                     'relations' => [
            //                         "question" => "answer",
            //                     ],
            //                 ],
            //             ],
            //         ]
            //     ],
            // ],
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

    public function searchIndex()
    {
        $params = [
            'index' => $this->_my_index,
        ];

        $result = $this->elasticsearch->indices()->get($params);

        return $result;
    }

    public function deleteIndex()
    {
        try {
            $response = $this->elasticsearch->indices()->delete(['index' => $this->_my_index]);

            return response()->json([
                "data" => [$response],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function createIndexDataParent()
    {
        // "name" => "Ariana Grande",
        // "relation_type" => ["name" => "parent"],
        $artists = [
            [
                'my_id' => 1,
                'body' => [
                    "text" => "This is a question",
                    "relation_type" => ["name" => "question"],
                ],
            ],
            [
                'my_id' => 2,
                'body' => [
                    "text" => "This is another question",
                    "relation_type" => ["name" => "question"],
                ],
            ],
        ];

        try {
            foreach ($artists as $artist) {
                $params = [
                    'index' => $this->_my_index,
                    'type' => $this->_my_type,
                    'id' => $artist['my_id'],
                    'refresh' => true,
                    'body' => $artist['body'],
                ];
                $response = $this->elasticsearch->create($params);
            }

            return response()->json([
                "data" => [$response],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function createIndexDataChild()
    {
        $songs = [
            [
                'id' => 3,
                'routing' => 1,
                'body' => [
                    "text" => "This is an answer",
                    "relation_type" => ["name" => "child", "parent" => 1],
                ],
            ],
            [
                'id' => 4,
                'routing' => 1,
                'body' => [
                    "text" => "This is another answer",
                    "relation_type" => ["name" => "child", "parent" => 1],
                ],
            ],
        ];

        try {

            foreach ($songs as $key => $song) {
                $params = [
                    'index' => $this->_my_index,
                    'type' => $this->_my_type,
                    'refresh' => true,
                    'id' => $song['id'],
                    'routing' => $song['routing'],
                    'body' => $song['body'],
                ];

                $response = $this->elasticsearch->create($params);
            }

            return response()->json([
                "data" => [$response],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function searchDocument()
    {
        $query = [
            // 'has_child' => [
            //     'type' => 'child',
            //     'query' => [
            //         'bool' => [
            //             'must' => [
            //                 'match' => ['song' => 'All of Me'],
            //             ],
            //         ],
            //     ],
            // ],
        ];

        $params = [
            'index' => $this->_my_index,
            'type' => $this->_my_type,
            'body' => [
                // "query" => [
                //     "has_child" => [
                //         "type" => "child",
                //         "query" => [
                //             "match_all" => [],
                //             "max_children" => 10,
                //             "min_children" => 2,
                //             "score_mode" => "min",
                //         ],
                //     ],
                // ],
                // "aggs" => [
                //     "parents" => [
                //         "terms" => [
                //             "field" => "relation_type#question",
                //             "size" => 10,
                //         ],
                //     ],
                // ],
                // "script_fields" => [
                //     "parent" => [
                //         "script" => [
                //             "source" => "relation_type#question",
                //         ],
                //     ],
                // ],
                // 'query' => [
                //     'bool' => [
                //         'must' => [
                //             'match' => ['song' => 'All of Me'],
                //         ],
                //     ],
                // ],
                // 'query' => [
                //     "has_parent" => [
                //         "parent_type" => "parent",
                //         "query" => [
                //             "match" => ["song" => "all of Me"],
                //         ],
                //     ],
                // ],
            ],
        ];

        try {
            $response = $this->elasticsearch->search($params);

            return response()->json([
                "data" => [$response],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }
}

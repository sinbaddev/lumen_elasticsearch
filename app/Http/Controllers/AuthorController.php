<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthorController extends Controller
{
    protected $request;
    protected $authorModel;
    protected $elasticsearch;
    private $_msgErrorNotExist = 'The index does not exist.';

    public function __construct(
        Request $request,
        Author $authorModel
    ) {
        $this->request = $request;
        $this->authorModel = $authorModel;
        $this->elasticsearch = ClientBuilder::create()->build();
    }

    public function createIndex()
    {
        $data = $this->authorModel->getDataCreateIndexListAuthor();
        $response = $this->elasticsearch->bulk($data);

        return response()->json([
            "data" => [
                'Create Index Successful',
            ],
        ], Response::HTTP_OK);
    }

    public function createDetailIndex($id)
    {
        $author = $this->authorModel->getDetail($id);

        $this->_processCreateNewIndex($author);

        return response()->json([
            "data" => [
                $author,
            ],
        ], Response::HTTP_OK);
    }

    public function index()
    {
        $isExistIndex = $this->elasticsearch->indices()->exists($this->authorModel->getParamIndex());
        if (!$isExistIndex) {
            return response()->json([
                "error" => $this->_msgErrorNotExist
            ], 404);
        }
        $input = $this->request->all();

        $params = $this->authorModel->getParamIndex(1, null, 1, $input);
       
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
        $params = $this->authorModel->getParamIndex(1, $id, 0, []);

        try {
            $author = $this->authorModel->getDetail($id);

            $isExistDoc = $this->elasticsearch->exists($params);
            if ($isExistDoc) {
                $get_doc = $this->elasticsearch->get($params);
            } else {
                $data = $this->authorModel->getDataIndex($author);

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

    public function store()
    {
        $input = $this->request->all();

        $author = $this->authorModel->store($input);

        $data = $this->authorModel->getDataIndex($author);

        $response = $this->elasticsearch->index($data);

        return response()->json([
            "data" => [
                $author,
            ],
        ], Response::HTTP_OK);
    }

    public function update($id)
    {
        // update data post
        $input = $this->request->all();
        $author = $this->authorModel->updateAuthor($id, $input);

        $params_exist = $this->authorModel->getParamIndex(1, $id, 0, []);

        $isExistDoc = $this->elasticsearch->exists($params_exist);
        if ($isExistDoc) {
            $data = $this->authorModel->getDataIndex($author, 1);

            $response = $this->elasticsearch->update($data);
        } else {
            $data = $this->authorModel->getDataIndex($author);

            $response = $this->elasticsearch->index($data);
        }

        return response()->json([
            "data" => [
                $author,
            ],
        ], Response::HTTP_OK);
    }

    public function deleteIndex()
    {
        $params = $this->authorModel->getParamIndex(0, null, 0, []);
        try {
            $isExistIndex = $this->elasticsearch->indices()->exists($params);
            if (!$isExistIndex) {
                return response()->json([
                    "error" => $this->_msgErrorNotExist
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

    public function deleteDetailIndex($id)
    {
        try {
            $params = $this->authorModel->getParamIndex(1, $id, 0, []);

            $isExistDoc = $this->elasticsearch->exists($params);
            if (!$isExistDoc) {
                return response()->json([
                    "error" => $this->_msgErrorNotExist
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
}

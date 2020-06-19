<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    protected $request;
    protected $orderModel;
    protected $elasticsearch;
    private $_msgErrorNotExist = 'The index does not exist.';
    public function __construct(
        Request $request,
        Order $orderModel
    ) {
        $this->request = $request;
        $this->orderModel = $orderModel;
        $this->elasticsearch = ClientBuilder::create()->build();
    }

    public function deleteIndex()
    {
        $params = $this->orderModel->getParamIndex();

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

    public function createIndex()
    {
        $data = $this->orderModel->createIndex();
        $response = $this->elasticsearch->bulk($data);
        return response()->json([
            "data" => [
                'Create Index Successful',
            ],
        ], Response::HTTP_OK);
    }
}

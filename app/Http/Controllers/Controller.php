<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

/**
 * @SWG\Swagger(
 *      schemes={"http"},
 *      host="lumen.search.api",
 *      basePath="/",
 *      produces={"application/json"},
 * 		consumes={"application/json"},
 *      @SWG\Info(
 *         version="2.0.0",
 *         title="Swagger for Lumen",
 *         description="Api description...",
 *      )
 * )
 */

class Controller extends BaseController
{
    private function getFractalManager()
    {
        $manager = new Manager();
        return $manager;
    }

    public function item($data, $transformer)
    {
        $manager = $this->getFractalManager();
        $resource = new Item($data, $transformer);
        return $manager->createData($resource)->toArray();
    }

    public function collection($data, $transformer)
    {
        $manager = $this->getFractalManager();
        $resource = new Collection($data, $transformer);
        return $manager->createData($resource)->toArray();
    }

    public function paginate($data, $transformer)
    {
        $manager = $this->getFractalManager();
        $resource = new Collection($data, $transformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($data));
        return $manager->createData($resource)->toArray();
    }
}

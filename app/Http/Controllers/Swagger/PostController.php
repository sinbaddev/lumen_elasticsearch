<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use App\Models\Swagger\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Transformers\Swagger\PostTransformer;

/**
 * @SWG\Tags(
 *      name="Post",
 *      description="Post Feature"
 * )
 */
class PostController extends Controller
{
    protected $request;
    protected $post;

    public function __construct(
        Request $request,
        Post $post
    ) {
        $this->request = $request;
        $this->post = $post;
    }

    /**
	 * @SWG\Get(
	 * 		path="/swagger/post/list",
	 * 		tags={"Post"},
	 * 		summary="Get list of posts",
     *      @SWG\Parameter(
	 * 			name="limit",
	 * 			in="query",
	 * 			type="integer",
	 * 			description="Limit",
	 * 		),
     *      @SWG\Parameter(
	 * 			name="page",
	 * 			in="query",
	 * 			type="integer",
	 * 			description="Page",
	 * 		),
     *      @SWG\Response(
     *          response="200",
     * 			description="success",
     *          @SWG\Schema(ref="#/definitions/Post")
     *      )
	 * 	)
	 */
    public function list()
    {
        $input = $this->request->all();
        $list = $this->post->getList($input);

        return $this->paginate($list, new PostTransformer());
    }

    /**
     * @SWG\Get(
     *      path="/swagger/post/{id}",
     *      tags={"Post"},
     *      summary="Get detail Post",
     *      @SWG\Parameter(
     *         description="Post Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *      ),
     *      @SWG\Response(
     *          response="200",
     * 			description="success",
     *          @SWG\Schema(ref="#/definitions/Post")
     *      )
     * )
     */
    public function detail($id)
    {
        $detail = $this->post->getDetail($id);

        return $this->item($detail, new PostTransformer());
    }
    
    public function store()
    {
        $input = $this->request->all();
        $post = $this->post->store($input);

        return $this->item($post, new PostTransformer());
    }
}
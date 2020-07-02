<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use App\Models\Swagger\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Transformers\Swagger\PostTransformer;
use App\Validator\Swagger\PostValidator;

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
     *      ),
     *      @SWG\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *      ),
     *      @SWG\Response(
     *         response="404",
     *         description="Post not found"
     *      ),
     * )
     */
    public function detail($id)
    {
        $detail = $this->post->getDetail($id);

        return $this->item($detail, new PostTransformer());
    }
    
    /**
     * @SWG\Post(
     *     path="/swagger/post",
     *     tags={"Post"},
     *     summary="Add a new post to the store",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Post object that needs to be added to the store",
     *         required=true,
     *         @SWG\Schema(
     *              @SWG\Property(property="title", type="string", description="Post title"),
     *              @SWG\Property(property="content", type="string", description="Post content")
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     * 			description="success",
     *          @SWG\Schema(ref="#/definitions/Post")
     *      ),
     *     @SWG\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     * )
     */
    public function store()
    {
        $input = $this->request->all();
        
        $post = $this->post->store($input);

        return $this->item($post, new PostTransformer());
    }

    /**
     * @SWG\Put(
     *     path="/swagger/post/{id}",
     *     tags={"Post"},
     *     summary="Update an existing post",
     *     @SWG\Parameter(
     *         description="Post Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *      ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Post object that needs to be updated to the store",
     *         required=true,
     *         @SWG\Schema(
     *              @SWG\Property(property="title", type="string", description="Post title"),
     *              @SWG\Property(property="content", type="string", description="Post content")
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     * 			description="success",
     *          @SWG\Schema(ref="#/definitions/Post")
     *      ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Post not found",
     *     ),
     *      @SWG\Response(
     *         response=422,
     *         description="Validation exception",
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Validation exception",
     *     ),
     * )
     */
    public function update($id)
    {
        $input = $this->request->all();
        $this->_validateInputPost($input);
        $post = $this->post->updatePost($id, $input);

        return $this->item($post, new PostTransformer());
    }

    private function _validateInputPost($input)
    {
        $validator = new PostValidator();
        $validator->validate($input);
    }

    /**
     * @SWG\Delete(
     *     path="/swagger/post/{id}",
     *     tags={"Post"},
     *     summary="Delete a post",
     *     @SWG\Parameter(
     *         description="Post id to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *      @SWG\Response(
     *          response="200",
     * 			description="Delete success"
     *      ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Post not found"
     *     ),
     * )
     */
    public function delete($id)
    {
        $post = $this->post->deletePost($id);

        return response()->json([
            "data" => [
                'Delete success',
            ],
        ], Response::HTTP_OK);
    }
}
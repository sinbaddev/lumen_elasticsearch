<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use App\Models\Swagger\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Transformers\Swagger\PostTransformer;

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

    public function list()
    {
        $list = $this->post->getList();

        return $this->paginate($list, new PostTransformer());
    }
}

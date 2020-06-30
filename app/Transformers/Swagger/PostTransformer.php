<?php

namespace App\Transformers\Swagger;

use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    public function transform($post)
    {
        return [
            'id' => object_get($post, 'id', 0),
            'title' => object_get($post, 'title', ''),
            'content' => object_get($post, 'content', ''),
            'created_at' => object_get($post, 'created_at', ''),
            'updated_at' => object_get($post, 'updated_at', ''),
        ];
    }
}
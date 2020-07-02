<?php

namespace App\Validator\Swagger;

use App\Validator\AbstractValidator;

class PostValidator extends AbstractValidator
{
    /**
     * @return array
     */
    protected function rules($params = [])
    {
        $rule = [
            'title'       => 'required',
            'content'   => 'required',
        ];

        return $rule;
    }

    public function messages()
    {
        return [
            'title.required'     => 'Title is required',
            'content.required'        => 'Content is required',
        ];
    }
}

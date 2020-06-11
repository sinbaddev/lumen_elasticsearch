<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
use App\Models\Post;

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->group(['prefix' => 'post'], function ($api) {
    $api->post('/create-index', 'PostController@createIndex');
    $api->post('/create-detail-index/{id:[0-9]+}', 'PostController@createDetailIndex');
    $api->get('/', 'PostController@index');
    $api->get('/{id:[0-9]+}', 'PostController@detail');
    $api->delete('/delete-index', 'PostController@deleteIndex');
    $api->post('/', 'PostController@store');
    $api->put('/{id:[0-9]+}', 'PostController@update');
    $api->delete('/{id:[0-9]+}/delete-index', 'PostController@deleteDetailIndex');
});

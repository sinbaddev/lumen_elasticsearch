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

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->group(['prefix' => 'post'], function ($api) {
    $api->post('/create-index', 'PostController@createIndex');
    $api->get('/', 'PostController@index');
    $api->get('/{id:[0-9]+}', 'PostController@detail');
    $api->delete('/delete-index', 'PostController@deleteIndex');
    $api->post('/', 'PostController@store');
    $api->delete('/{id:[0-9]+}/delete-index', 'PostController@deleteDetailIndex');
    $api->put('/{id:[0-9]+}', 'PostController@update');
});

$app->group(['prefix' => 'author'], function ($api) {
    $api->post('/create-index', 'AuthorController@createIndex');
    $api->get('/', 'AuthorController@index');
    $api->get('/{id:[0-9]+}', 'AuthorController@detail');
    $api->delete('/delete-index', 'AuthorController@deleteIndex');
    $api->post('/', 'AuthorController@store');
    $api->delete('/{id:[0-9]+}/delete-index', 'AuthorController@deleteDetailIndex');
    $api->put('/{id:[0-9]+}', 'AuthorController@update');
});

$app->group(['prefix' => 'parent-child'], function ($api) {
    $api->post('/create-index', 'ParentChildController@createIndex');
    $api->delete('/delete-index', 'ParentChildController@deleteIndex');
    $api->post('/create-index-data-parent', 'ParentChildController@createIndexDataParent');
    $api->post('/create-index-data-child', 'ParentChildController@createIndexDataChild');
    $api->put('/update-index', 'ParentChildController@updateIndex');
    $api->get('/search-index', 'ParentChildController@searchIndex');
    $api->get('/search-document', 'ParentChildController@searchDocument');
});

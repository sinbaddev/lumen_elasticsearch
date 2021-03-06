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

$app->group(['prefix' => 'swagger', 'namespace' => 'Swagger'], function ($api) {
    $api->group(['prefix' => 'post'], function ($api) {
        $api->get('/list', 'PostController@list');
    });
});

$app->group(['prefix' => 'post'], function ($api) {
    /* */
    $api->get('/list-post', 'PostController@getListPost');
    /* Index */
    $api->post('/create-index', 'PostController@createIndex');
    $api->delete('/delete-index', 'PostController@deleteIndex');
    $api->get('/search-index', 'PostController@searchIndex');
    /* Document */
    $api->post('/create-list-document', 'PostController@createListDocument');
    $api->get('/search-document-pagination', 'PostController@searchDocumentPagination');
    $api->post('/create-document', 'PostController@createDocument');
    $api->put('/update-document/{id:[0-9]+}', 'PostController@updateDocument');
    $api->delete('/delete-document/{id:[0-9]+}', 'PostController@deleteDocument');
    $api->get('/detail-document/{id:[0-9]+}', 'PostController@detailDocument');
    $api->get('/search-document-match', 'PostController@searchDocumentMatch');
    $api->get('/search-show-field-expect', 'PostController@searchAndShowFieldExpect');
});

$app->group(['prefix' => 'compound-query'], function ($api) {
    $api->get('/search-bool', 'CompoundQueryController@searchBool');
    $api->get('/search-boosting', 'CompoundQueryController@searchBoosting');
    $api->get('/search-constant-score', 'CompoundQueryController@searchConstantScore');
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

$app->group(['prefix' => 'order'], function ($api) {
    $api->post('/create-index', 'OrderController@createIndex');
    $api->delete('/delete-index', 'OrderController@deleteIndex');
});

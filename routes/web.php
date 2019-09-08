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



$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->group(['prefix' => '/vidm/v1/metadata'], function () use($router) {
    $router->get('/', 'metadataController@index');
    $router->get('/{id}', 'metadataController@index');
    $router->post('/store', 'metadataController@store');
    $router->post('/search', 'metadataController@search');
    $router->post('/update/{id}', 'metadataController@update');
    $router->get('/delete/{id}', 'metadataController@delete');
    $router->get('/download/{id}', 'metadataController@addDownload');
    $router->get('/save/{id}', 'metadataController@addSave');
    $router->get('/share/{id}', 'metadataController@addShare');
    $router->get('/view/{id}', 'metadataController@addViewer');
    $router->post('/like/{id}', 'reactController@like');
    $router->post('/dislike/{id}', 'reactController@dislike');
});

$router->group(['prefix' => '/vidm/v1/metavideos'], function () use($router) {
    $router->get('/{id}', 'metavideoController@index');
    $router->post('/store/{id}', 'metavideoController@create');
    $router->post('/update', 'metavideoController@update');
    $router->post('/delete', 'metavideoController@destroy');
});

$router->group(['prefix' => '/vidm/v1/metadata/sub'], function () use($router) {
    $router->get('/{id}', 'subtitleController@index');
    $router->post('/store/{id}', 'subtitleController@create');
    $router->post('/update', 'subtitleController@update');
    $router->post('/delete', 'subtitleController@destroy');
});

$router->group(['prefix' => '/vidm/v1/metadata/comment'], function () use($router) {
    $router->get('/{id}', 'commentController@index');
    $router->post('/add/{id}', 'commentController@create');
    $router->post('/update', 'commentController@update');
    $router->post('/delete', 'commentController@destroy');
});
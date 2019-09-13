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

$router->group(['prefix' => '/vidm/v1/metavideos'], function () use($router) {
    $router->get('/{id}', 'metavideoController@index');
    $router->post('/store/{id}', 'metavideoController@create');
    $router->post('/update', 'metavideoController@update');
    $router->post('/delete', 'metavideoController@destroy');
});
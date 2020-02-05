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

$router->group(['prefix' => env('API_PREFIX'), 'namespace' => 'Api'], function () use ($router)
{

    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');

    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {

        $router->get('/profile', 'AuthController@profile');

        $router->get('/projects', 'ProjectController@index');
        $router->post('/project', 'ProjectController@create');
        $router->get('/project/{id}', 'ProjectController@show');
        $router->put('/project/{id}', 'ProjectController@update');
        $router->delete('/project/{id}', 'ProjectController@delete');

        $router->get('/tasks', 'TaskController@index');
        $router->post('/task', 'TaskController@create');
        $router->get('/task/{id}', 'TaskController@show');
        $router->put('/task/{id}', 'TaskController@update');
        $router->put('/task/{id}/close', 'TaskController@close');
        $router->delete('/task/{id}', 'TaskController@delete');

    });

});

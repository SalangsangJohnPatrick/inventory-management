<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/inventory', 'InventoryController@index');
$router->get('/inventory/{id}', 'InventoryController@show');
$router->post('/inventory', 'InventoryController@store');
$router->put('/inventory/{id}', 'InventoryController@update');
$router->delete('/inventory/{id}', 'InventoryController@destroy');

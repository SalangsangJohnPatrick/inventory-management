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

$router->get('/GetAllInventoryItems', 'InventoryController@index');
$router->get('/GetSpecificInventoryItem/{id}', 'InventoryController@show');
$router->get('/SortInventoryItems/{column}/{order}', 'InventoryController@sort');
$router->get('/InventoryValuationReport/{type}', 'InventoryController@valuationReport');
$router->get('/GetTopSellingProducts', 'InventoryController@getTopSellingProducts');
$router->get('/GetLowStockItems', 'InventoryController@getLowStockItems');
$router->post('/CreateInventoryItem', 'InventoryController@store');
$router->post('/ImportInventoryItems', 'InventoryController@import');
$router->put('/UpdateInventoryItem/{id}', 'InventoryController@update');
$router->delete('/DeleteInventoryItem/{id}', 'InventoryController@destroy');


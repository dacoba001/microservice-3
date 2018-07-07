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
    //return $app->version();
    return "<center><h1>Servidor 3</h1><br>Carrito<br>Pedido</center>";
});

$app->get('/carritos', 'CarritosController@index');
$app->get('/carritos/cliente/{users}', 'CarritosController@getCliente');
$app->post('/carritos', 'CarritosController@createCarrito');
$app->delete('/carritos/{carritos}', 'CarritosController@destroyCarrito');

$app->get('/pedidos', 'PedidosController@index');
$app->get('/pedidos/estado/{estado}', 'PedidosController@getEstado');
$app->get('/pedidos/{pedidos}', 'PedidosController@showPedido');
$app->get('/pedidos/detalle/{pedidos}', 'PedidosController@getDetalle');
$app->get('/pedidos/cliente/{users}', 'PedidosController@getCliente');
$app->get('/pedidos/cliente/pendiente/{users}', 'PedidosController@getClientePendiente');
$app->get('/pedidos/cliente/entregado/{users}', 'PedidosController@getClienteEntregado');
$app->post('/pedidos', 'PedidosController@createPedido');
$app->put('/pedidos/validar/{pedidos}', 'PedidosController@pedidoValidar');

$app->get('/detallepedido/{detallepedido}', 'PedidosController@getDetallepedido');
$app->put('/detallepedido/entregar/{detallepedido}', 'PedidosController@putEntregarDetallepedido');
$app->put('/detallepedido/devolver/{detallepedido}', 'PedidosController@putDevolverDetallepedido');

$app->get('/importacions', 'ImportacionsController@index');
$app->post('/importacions', 'ImportacionsController@createImportacion');
$app->put('/importacions/{importacions}', 'ImportacionsController@updateImportacion');
$app->delete('/importacions/{importacions}', 'ImportacionsController@destroyImportacion');

$app->post('/reportes/pedido', 'PedidosController@reportePedido');
$app->post('/reportes/importacion', 'ImportacionsController@reporteImportacion');
$app->post('/reportes/stocks', 'PedidosController@reporteStock');
$app->post('/reportes/clientes', 'PedidosController@reporteCliente');

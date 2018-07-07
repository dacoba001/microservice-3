<?php
$app->get('/usuarios', 'UsuariosController@index');
$app->get('/usuarios/{usuarios}', 'UsuariosController@getUsuario');
$app->post('/usuarios', 'UsuariosController@createUsuario');
?>
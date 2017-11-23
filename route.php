<?php

use Middleware\Autenticar;

//usuario
$app->get('/login', function ($request, $response, $args) {
    session_destroy();
    unset($_SESSION);
    
    return $this->view->render($response, 'login.html');
});
$app->post('/usuario/registrar', 'Controller\Usuario:add');
$app->post('/usuario/login', 'Controller\Usuario:logar');


$app->group('/', function () use ($app) {
    
    //pagina inicial
    $app->get('', 'Controller\Home:index');


    //grupos
    $app->get('grupo', 'Controller\Grupo:index');
    $app->post('grupo/add', 'Controller\Grupo:add');
    $app->post('grupo/del', 'Controller\Grupo:del');
    $app->post('grupo/edit', 'Controller\Grupo:edit');
    $app->get('grupo/{id}', 'Controller\Grupo:visualizar');
    $app->post('grupo/cancelar', 'Controller\Grupo:cancelar');
    $app->get('grupo/participantes/filtro', 'Controller\Grupo:filtro');
    $app->post('grupo/participantes/remover', 'Controller\Grupo:participanteRemover');
    $app->post('grupo/participantes/permissao', 'Controller\Grupo:permissao');
    $app->post('grupo/convidar', 'Controller\Grupo:convidar');
    $app->post('grupo/aceitar', 'Controller\Grupo:aceitarConvite');

    //sessões
    $app->get('sessao', 'Controller\Sessao:index');
    $app->post('sessao/add', 'Controller\Sessao:add');
    $app->post('sessao/del', 'Controller\Sessao:del');
    $app->get('sessao/{id}', 'Controller\Sessao:visualizar');

    //loja
    $app->get('loja', 'Controller\Loja:index');

})->add(new Middleware\Autenticar);
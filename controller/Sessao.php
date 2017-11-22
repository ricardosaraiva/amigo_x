<?php

namespace Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use  Illuminate\Database\Capsule\Manager as DB;


class Sessao {

    private $c = null;
    
    public function __construct($container)
    {
        $this->c = $container;
    }
    
    public function index(Request $request, Response $response) {
    	$grupos = \Model\Grupo::
    		join('grupo_usuario', 'grupo.id', 'grupo_usuario.id_grupo')->
    		where('grupo_usuario.id_usuario', '=', $_SESSION['id'])->
    		where('grupo_usuario.status', '=', 1)->
    		whereIn('grupo_usuario.permissao', ['administrador', 'dono'])->
    		select('grupo.nome', 'grupo.id')->
    	get();

        $this->c['view']->render($response, 'sessao.html', [
        	'grupos' => $grupos
        ]);
    }
}
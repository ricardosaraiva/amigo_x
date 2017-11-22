<?php

namespace Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use  Illuminate\Database\Capsule\Manager as DB;

class Home {

    private $c = null;
    
    public function __construct($container)
    {
        $this->c = $container;
    }
    
    public function index(Request $request, Response $response) {
        $grupos = \Model\Grupo::join('grupo_usuario', 'grupo_usuario.id_grupo', '=', 'grupo.id')->
            select('grupo.id', 'grupo.nome')->
            where('grupo_usuario.id_usuario','=', $_SESSION['id'])->
            where('grupo_usuario.status','=', 0)->
        get();

        $this->c['view']->render($response, 'home.html',[
            'grupos' => $grupos,
            'total' => count($grupos)
        ]);
    }
}
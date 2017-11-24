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
            where('grupo.status','=', 1)->
        get();

        $sessao = \Model\Sessao::
            join('grupo_usuario', 'grupo_usuario.id_grupo','=','sessao.id_grupo')->
            join('sessao_usuario', 'sessao.id','=','sessao_usuario.id_sessao')->
            where('sessao.status','=','1')->
            where('sessao_usuario.id_usuario','=',$_SESSION['id'])->
            where('grupo_usuario.id_usuario','=',$_SESSION['id'])->
            select('sessao.id', 'sessao.descricao', 'sessao.data', 'grupo_usuario.permissao')->
        get();

        $this->c['view']->render($response, 'home.html',[
            'grupos' => $grupos,
            'sessoes' => $sessao
        ]);
    }
}
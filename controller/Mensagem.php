<?php

namespace Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use  Illuminate\Database\Capsule\Manager as DB;

class Mensagem {

    private $c = null;
    
    public function __construct($container)
    {
        $this->c = $container;
    }
    
    public function chat(Request $request, Response $response) {
        $sessao = \Model\Sessao::
            join('grupo_usuario', 'grupo_usuario.id_grupo','=','sessao.id_grupo')->
            join('sessao_usuario', 'sessao.id','=','sessao_usuario.id_sessao')->
            where('sessao.status','=','1')->
            where('sessao_usuario.id_usuario','=',$_SESSION['id'])->
            where('grupo_usuario.id_usuario','=',$_SESSION['id'])->
            select('sessao.id', 'sessao.descricao')->
        get();

        return $response->withJson($sessao);
    }

    public function msg(Request $request, Response $response) {
        $idMsg = (int) $request->getParam('ultimaMsg'); 
        $sessao = (int) $request->getParam('sessao'); 
        
        $msg = \Model\SessaoChat::
            join('usuario', 'usuario.id', '=', 'sessao_chat.id_usuario')->
            where('sessao_chat.id_sessao', '=' , $sessao)->
            where('sessao_chat.id','>', $idMsg)->
            select(
                'sessao_chat.id', 
                'sessao_chat.msg', 
                'usuario.id as id_usuario',
                'usuario.nome', 
                'sessao_chat.created_at as data'
            )->
        get();

        return $response->withJson($msg);
    }

    function add(Request $request, Response $response) {
        $dados = $request->getParsedBody();
        $idMsg = (int) $dados['ultimaMsg']; 
        $sessao = (int) $dados['sessao']; 
        $mensagem = strip_tags(trim($dados['msg']));

        try {
            if (!empty($mensagem)) {
                $msg = new \Model\SessaoChat;
                $msg->id_sessao = $sessao;
                $msg->id_usuario = $_SESSION['id'];
                $msg->msg = nl2br($mensagem);
                $msg->save();
            }

            return $response->withJson('Mensagem enviada com sucesso!');
        } catch( \Exception $e) {
            echo $e;
            return $response->withJson('Erro ao enviar mensagem!', 400);
        }
        
    }
}
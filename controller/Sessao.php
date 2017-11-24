<?php

namespace Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use  Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator;
use Helpers\Email;

class Sessao {

    private $c = null;
    
    public function __construct($container)
    {
        $this->c = $container;
    }
    
    public function index(Request $request, Response $response) {

        $grupoUsuario = \Model\GrupoUsuario::
            where('status','=', 1)->
            select('id_grupo')->
            groupBy('id_grupo')->
            having( DB::raw('COUNT(*)'), '>', 2)->
        get();

        $grupos = \Model\Grupo::
    		join('grupo_usuario', 'grupo.id', 'grupo_usuario.id_grupo')->
    		where('grupo_usuario.id_usuario', '=', $_SESSION['id'])->
            where('grupo_usuario.status', '=', 1)->
            where('grupo.status', '=', 1)->
            whereIn('grupo_usuario.permissao', ['administrador', 'dono'])->
            whereIn('grupo_usuario.id_grupo', $grupoUsuario)->
    		select('grupo.nome', 'grupo.id')->
        get();

        //Sessão
        $sessao = \Model\Sessao::
            join('grupo_usuario', 'grupo_usuario.id_grupo','=','sessao.id_grupo')->
            join('sessao_usuario', 'sessao.id','=','sessao_usuario.id_sessao')->
            where('sessao.status','=','1')->
            where('sessao_usuario.id_usuario','=',$_SESSION['id'])->
            where('grupo_usuario.id_usuario','=',$_SESSION['id'])->
            select('sessao.id', 'sessao.descricao', 'sessao.data', 'grupo_usuario.permissao')->
        get();


        $this->c['view']->render($response, 'sessao.html', [
            'grupos' => $grupos,
            'sessoes' => $sessao
        ]);
    }

    public function add(Request $request, Response $response) {
        $dados = $request->getParsedBody();

        //valida se o usuario tem acesso para adicionar uma sessão de amigo segreto 
        $permissao = \Model\Grupo::getPermissao($_SESSION['id'], $dados['grupo']);
        if(!in_array($permissao, ['dono', 'administrador'])) {
            return $response->withJson([
                'erro' => 'Grupo inválido!'
            ], 400);    
        }

        if(!Validator::length(6,100)->validate(strip_tags($dados['descricao']))) {
            return $response->withJson(
                [
                    'descricao' => 'O campo descrição contém um valor inválido!'
            ], 400);
        }

        if(!Validator::date()->validate(strip_tags($dados['data']))) {
            return $response->withJson(
                [
                    'descricao' => 'O campo data contém uma data inválida!'
            ], 400);
        }

        $grupo = \Model\Grupo::
            where('status','=', 1)->
            where('id','=', $dados['grupo'])->
            select('id')->
        get();

        if(!isset($grupo[0]->id)) {
            return $response->withJson(
                [
                    'grupo' => 'Grupo inválido!'
            ], 400);
        }

        $usuarios = \Model\GrupoUsuario::
            join('usuario', 'usuario.id', 'grupo_usuario.id_usuario')->
            where('id_grupo','=', $dados['grupo'])->
            where('status','=', 1)->
            select('usuario.id', 'usuario.nome', 'usuario.email')->
        get();

        //valida se tem mais de 3 usuários no grupo para criar uma sessão de amigo secreto
        if ( count($usuarios) < 3) {
            return $response->withJson(
                [
                    'grupo' => 'O grupo selecionado tem menos de 3 usuários, para iniciar uma sessão de amigo x é necessario no mínimo 3 usuários!'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $sessao = new \Model\Sessao;
            $sessao->id_usuario = $_SESSION['id'];
            $sessao->id_grupo = (int) $dados['grupo'];
            $sessao->descricao = strip_tags(trim($dados['descricao']));
            $sessao->local = strip_tags($dados['local']);
            $sessao->data = \Helpers\Formatar::dataDb($dados['data']);
            $sessao->status = 1;
            $sessao->obs = strip_tags($dados['obs']);
            $sessao->save();

            $sortear = [];
            foreach ($usuarios as $usuario) {
                $sortear[] = $usuario;
            }

            $participantes = \Helpers\Sorteio::sortear($sortear);
            

            //grava os usuario do sorteio
            foreach ($participantes  as $participante => $amigo) {
                $sessaoUsuario = new \Model\SessaoUsuario;
                $sessaoUsuario->id_sessao = $sessao->id;
                $sessaoUsuario->id_usuario = $participante;
                $sessaoUsuario->id_amigo_secreto = $amigo->id;
                $sessaoUsuario->save();

                //envia o e-mail
                $email = new Email($this->c);
                $email->Subject = 'AMIGO X notificações!';
                $email->Body = 'Nova sessaõ de amigo x criada acesse AMIGO X para ver.';
                $email->AddAddress($amigo->email);
        
                if(!$email->Send()) { 
                    $email->Send(); //caso der erro tenta enviar o e-mail novamente
                } 
            }
            
            $data = new \DateTime($dados['data']);
            DB::commit();
            return $response->withJson([
                'id' => $sessao->id,
                'descricao' => strip_tags(trim($dados['descricao'])),
                'data' => $data->format('d/m/Y H:i'),
            ]);

        } catch( \Exception $e ) {
            echo $e;
            DB::rollback();
            return $response->withJson(
                [
                    'erro' => 'Ocorreu um erro inesperado!'
            ], 400);   
        }

        
        
    }

    public function del(Request $request, Response $response) {
        $dados = $request->getParsedBody();

        $sessao = \Model\Sessao::
            where('id','=', $dados['id'])->
            where('status','=', 1)->
            select('id_grupo')->
        get();


        if(!($sessao[0]->id_grupo)) {
            return $response->withJson([
                'erro' => 'Ocorreu um erro inesperado!'
            ], 400);    
        }
        
        //valida se o usuario tem acesso para cancelar a sessão de amigo segreto 
        $permissao = \Model\Grupo::getPermissao($_SESSION['id'], $sessao[0]->id_grupo);
        if(!in_array($permissao, ['dono', 'administrador'])) {
            return $response->withJson([
                'erro' => 'Ocorreu um erro inesperado!'
            ], 400);    
        }

        \Model\Sessao::
            where('id','=', $dados['id'])->
        update([
            'status' => 0
        ]);

        return $response->withJson('Sessão cancelada com sucesso!');
    }

    public function visualizar(Request $request, Response $response, $args) {
        $sessao =  \Model\Sessao::
            where('id', '=', (int) $args['id'])->
            where('status', '=', 1)->
            select('id', 'descricao', 'local', 'data', 'obs')->
        get();

        if(!isset($sessao[0]->id)) {
            header('location: /sessao');
            exit;
        }

        $participantes =  \Model\Usuario::
            join('sessao_usuario', 'usuario.id', '=', 'sessao_usuario.id_usuario')->
            where('sessao_usuario.id_sessao', '=', (int) $args['id'])->
            select('usuario.nome', 'sessao_usuario.id_usuario')->
        get();

        $amigo =  \Model\Usuario::
            join('sessao_usuario', 'usuario.id', '=', 'sessao_usuario.id_amigo_secreto')->
            where('sessao_usuario.id_sessao', '=', (int) $args['id'])->
            where('sessao_usuario.id_usuario', '=', $_SESSION['id'])->
            select('usuario.nome')->
        get();

        if(!isset($amigo[0]->nome)) {
            header('location: /sessao');
            exit;
        }


        $data = new \DateTime($sessao[0]->data);
        return $this->c['view']->render($response, 'sessao_visualizar.html', [
            'codigo' => $sessao[0]->id,
            'nome'=> $sessao[0]->nome,
            'descricao'=> $sessao[0]->descricao,
            'local'=> $sessao[0]->local,
            'data'=> $data->format('d/m/Y H:i'),
            'obs'=> $sessao[0]->obs,
            'participantes' => $participantes,
            'amigo' => $amigo[0]->nome
        ]);
    }
}
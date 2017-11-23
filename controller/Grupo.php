<?php

namespace Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use  Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator;
use Model\GrupoUsuario;
use Helpers\Email;


class Grupo {

    private $c = null;
    
    public function __construct($container)
    {
        $this->c = $container;
    }
    
    public function index(Request $request, Response $response) {
        $grupos = \Model\Grupo::join('grupo_usuario', 'grupo_usuario.id_grupo', '=', 'grupo.id')->
            select('grupo.id', 'grupo.nome', 'grupo_usuario.permissao')->
            where('grupo_usuario.id_usuario','=', $_SESSION['id'])->
            where('grupo_usuario.status','=', 1)->
            where('grupo.status','=', 1)->
        get();

        $this->c['view']->render($response, 'grupo.html',[
            'grupos' => $grupos
        ]);
    }

    public function add(Request $request, Response $response) {
        
        $dados = $request->getParsedBody();

        if(!Validator::length(3,100)->validate(strip_tags($dados['nome']))) {
            return $response->withJson(
                [
                    'nome' => 'O campo Nome contém um valor inválido!'
            ], 400);
        }

        if(isset($validarGrupo[0]->id)) {
            return $response->withJson(
                [
                    'nome' => 'Você já criou um grupo com este nome!'
            ], 400); 
        }

        
        try {
            DB::beginTransaction();
            $grupo = new \Model\Grupo;
            $grupo->nome = strip_tags(trim($dados['nome']));
            $grupo->status = 1;
            $grupo->save();
    
            $grupoUsuario = new \Model\GrupoUsuario;
            $grupoUsuario->id_grupo = $grupo->id;
            $grupoUsuario->id_usuario = $_SESSION['id'];
            $grupoUsuario->permissao = 'dono';
            $grupoUsuario->status = 1;
            $grupoUsuario->save();
            DB::commit();
            return $response->withJson([
                'codigo' => $grupo->id, 'nome' => $grupo->nome, 'permissao' => 'dono'
            ]);
        } catch(\Exception $e) {
            DB::rollback();

            return $response->withJson([
                    'erro' => 'Ocorreu um erro inesperado!'
            ], 400);
        }
    
    }

    public function edit(Request $request, Response $response) {
        $dados = $request->getParsedBody();
        
        if(!Validator::length(3,100)->validate(strip_tags($dados['nome']))) {
            return $response->withJson(
                [
                    'nome' => 'O campo Nome contém um valor inválido!'
            ], 400);
        }

        $permissao = \Model\Grupo::getPermissao($_SESSION['id'],$dados['id']);
        if(!in_array($permissao, ['dono', 'administrador'])) {
            return $response->withJson([
                'erro' => 'Ocorreu um erro inesperado!'
        ], 400);    
        }

        try {
            \Model\Grupo::where('id', '=', ( (int) $dados['id']))->update([
                'nome' => strip_tags($dados['nome'])
            ]);

            return $response->withJson([
                'codigo' => $dados['id'], 'nome' => $dados['nome'], 'permissao' => $permissao
            ]);
        } catch(\Exception $e) {
            return $response->withJson([
                    'erro' => 'Ocorreu um erro inesperado!'
            ], 400);
        }
    }

    public function del(Request $request, Response $response) {
        $dados = $request->getParsedBody();

        if(\Model\Grupo::getPermissao($_SESSION['id'],$dados['id']) !=  'dono') {
            return $response->withJson([
                'erro' => 'Ocorreu um erro inesperado!'
        ], 400);    
        }
        
        try {
            \Model\Grupo::where('id', '=', ( (int) $dados['id']))->update([
                'status' => 0
            ]);
            return $response->withJson('Grupo removido com sucesso!');
        } catch(\Exception $e) {
            return $response->withJson([
                    'erro' => 'Ocorreu um erro inesperado!'
            ], 400);
        }
    }

    public function visualizar(Request $request, Response $response, $args) {

        $grupos = \Model\Grupo::join('grupo_usuario', 'grupo_usuario.id_grupo', '=', 'grupo.id')->
            select('grupo.id', 'grupo.nome', 'grupo_usuario.permissao')->
            where('grupo_usuario.id_usuario','=', $_SESSION['id'])->
            where('grupo.id','=', (int) $args['id'])->
            where('grupo.status','=', 1)->
        get();

        //valida se o grupo é valido e o usuario tem acesso ao grupo
        if( !isset($grupos[0]->id) ) {
            header('location: /grupo');
            exit;
        }

        $usuario = \Model\Usuario::
            join('grupo_usuario', 'grupo_usuario.id_usuario', '=', 'usuario.id')->
            where('grupo_usuario.id_grupo','=', (int) $args['id'])->
            select(
                'usuario.id',
                'usuario.nome',
                'usuario.email',
                'grupo_usuario.status',
                'grupo_usuario.permissao'
            )->
            orderBy('grupo_usuario.permissao')->
        get();

        $this->c['view']->render($response, 'grupo_visualizar.html',[
            'id' => $_SESSION['id'],
            'codigo' => $grupos[0]->id,
            'nome' => $grupos[0]->nome,
            'permissao' => $grupos[0]->permissao,
            'usuarios' => $usuario
        ]);
    }

    public function cancelar(Request $request, Response $response) {
        $dados = $request->getParsedBody();

        \Model\GrupoUsuario::
            where('id_grupo', '=', $dados['id'])->
            where('id_usuario', '=', $_SESSION['id'])->
        delete();

        return $response->withJson('Convite do grupo removido com sucesso!');
    }

    public function participanteRemover(Request $request, Response $response) {
        $dados = $request->getParsedBody();

        //valida se o usaurario tem acesso para fitlrar 
        $permissao = \Model\Grupo::getPermissao($_SESSION['id'],$dados['grupo']);
        if(!in_array($permissao, ['dono', 'administrador'])) {
            return $response->withJson([]);    
        }

        \Model\GrupoUsuario::
            where('id_grupo', '=', $dados['grupo'])->
            where('id_usuario', '=', $dados['usuario'])->
        delete();

        return $response->withJson('Convite do grupo removido com sucesso!');
    }

    public function filtro(Request $request, Response $response, $args) {
        $filtro = $request->getParam('filtro');        
        $grupo = $request->getParam('grupo');    
        
        //valida se o usuario tem acesso para fitlrar 
        $permissao = \Model\Grupo::getPermissao($_SESSION['id'],$grupo);
        if(!in_array($permissao, ['dono', 'administrador'])) {
            return $response->withJson([]);    
        }

        $usuario = \Model\Usuario::
            where(function ($w) use ($filtro) {
                $w->orWhere('nome', 'like', '%' . $filtro . '%');
                $w->orWhere('email', 'like', '%' . $filtro . '%');
            })->
            whereNotIn('id', function ($q) use ($grupo) {
                $q->select('id_usuario')
                ->from(with(new \Model\GrupoUsuario)->getTable())
                ->where('id_grupo', '=', $grupo);    
            })->
            select('id', 'nome', 'email')->
            limit(10)->
        get();

        return $response->withJson($usuario);
    }

    public function convidar(Request $request, Response $response) {
        $dados = $request->getParsedBody();
        
        $idUsuario = $dados['usuario'];
        $grupo = $dados['grupo'];

        $usuario = \Model\Usuario::
            where('id', '=', $idUsuario)->
            select('id', 'nome', 'email')->
        get();

        //valida se o usaurario tem acesso para convidar outro 
        $permissao = \Model\Grupo::getPermissao($_SESSION['id'], $grupo);
        if(!in_array($permissao, ['dono', 'administrador']) || empty($usuario[0]->id)) {
            return $response->withJson([ 
                'erro' => 'Ocorreu um erro inesperado!'
            ] , 400);    
        }

        //valida se o usúario ja esta cadastrado
        $grupoUsuario = \Model\GrupoUsuario::
            where('id_usuario','=', $idUsuario)->
            where('id_grupo','=', $grupo)->
            select('id')->
        get();

        if(isset($grupoUsuario[0]->id)) {
            return $response->withJson([ 
                'erro' => 'O usuário já esta neste grupo!'
            ] , 400);    
        }

        $grupoUsuario = new \Model\GrupoUsuario;
        $grupoUsuario->id_usuario = $idUsuario;
        $grupoUsuario->permissao = 'participante';
        $grupoUsuario->id_grupo = $grupo;
        $grupoUsuario->status = 0;
        $grupoUsuario->save();


        $email = new Email($this->c);
        $email->Subject = 'AMIGO X notificações!';
        $email->Body = 'Você tem um convite para um novo grupo acesse AMIGO X para ver o convite.';
        $email->AddAddress($usuario[0]->email);

        if(!$email->Send()) { 
            
            return $response->withJson([ 
                'erro' => 'Ocorreu um erro inesperado!'
            ] , 400);  

        } else {
            return $response->withJson('Registro cadastrado com sucesso!');   
        }
    }

    public function permissao(Request $request, Response $response) {
        $dados = $request->getParsedBody();

        //valida se o usaurario tem acesso para alterar a permissão do outro usuario
        $permissao = \Model\Grupo::getPermissao($_SESSION['id'],$dados['grupo']);
        if($permissao != 'dono') {
            return $response->withJson([ 
                'erro' => 'Ocorreu um erro inesperado!'
            ] , 400);    
        }

        if(!in_array($dados['permissao'],  ['participante', 'administrador'])) {
            return $response->withJson([ 
                'permissao' => 'Nivel de permissão inválida!'
            ] , 400);    
        }

        \Model\GrupoUsuario::
            where('id_usuario','=', $dados['usuario'])->
            where('id_grupo','=', $dados['grupo'])->
        update([
            'permissao' => $dados['permissao']
        ]);

        return $response->withJson('Permissão do usuário atualizada!'); 
    }

    public function aceitarConvite(Request $request, Response $response) {
        $dados = $request->getParsedBody();
        
        $grupoUsuario = \Model\GrupoUsuario::
            where('id_usuario','=', $_SESSION['id'])->
            where('id_grupo','=', $dados['id'])->
            where('status','=', 0)->
            select('id')->
        get();

        if(!isset($grupoUsuario[0]->id)) {
            return $response->withJson([ 
                'erro' => 'Convite inválido!'
            ] , 400);    
        }

        $grupoUsuario = \Model\GrupoUsuario::
        where('id_usuario','=', $_SESSION['id'])->
        where('id_grupo','=', $dados['id'])->
        update([
            'status' => 1
        ]);

        return $response->withJson('Convite aceito com sucesso!');   
    }

}
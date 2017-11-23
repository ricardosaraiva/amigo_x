<?php

namespace Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Respect\Validation\Validator;


class Usuario {

    public function logar(Request $request, Response $response) {
        session_destroy();
        unset($_SESSION);

        $dados = $request->getParsedBody();
        $usuario = \Model\Usuario::
            where('email', '=', $dados['email'])->
            select('id', 'nome', 'email', 'senha')->
        get();

        if(!isset($usuario[0]->id)) {
            return $response->withJson(
                [
                    'erro' => 'Usuário ou senha inválidos!'
            ], 400);
        }

        if(!password_verify($dados['senha'], $usuario[0]->senha)) {
            return $response->withJson(
                [
                    'erro' => 'Usuário ou senha inválidos!'
            ], 400);
        }

        session_start();
        $_SESSION['id'] = $usuario[0]->id;
        $_SESSION['nome'] = $usuario[0]->nome;
        $_SESSION['email'] = $usuario[0]->email;
        $_SESSION['navegador'] = $_SERVER['HTTP_USER_AGENT'];

        return $response->withJson('Usuário logado com sucesso!');
    }

    public function add(Request $request, Response $response) {

        $dados = $request->getParsedBody();

        
        if(!Validator::length(3,100)->validate($dados['novoNome'])) {
            return $response->withJson(
                [
                    'novoNome' => 'O campo Nome contém um valor inválido!'
            ], 400);
        }

        if(!Validator::email()->validate($dados['novoEmail'])) {
            return $response->withJson(
                [
                    'novoEmail' => 'O campo E-mail contém um valor inválido!'
            ], 400);
        }

        if(!Validator::length(6,16)->validate($dados['novaSenha'])) {
            return $response->withJson(
                [
                    'novaSenha' => 'O campo Senha deve conter de 6 á 16 caracteres!'
            ], 400);
        }

        if($dados['novaSenha'] !=  $dados['novaSenhaConfirmar']) {
            return $response->withJson(
                [
                    'novaSenhaConfirmar' => 'As senhas não coincidem!'
            ], 400);
        }

        //valida se o e-mail não esta duplicado
        $validar = \Model\Usuario::
            where('email','=', $dados['novoEmail'])->
            select('id')->
        get();

        if(isset($validar[0]->id)) {
            return $response->withJson(
                [
                    'novoEmail' => 'Já existe um usuário com este e-mail cadastrado!'
                ], 400);
        }



        $usuario = new \Model\Usuario;
        $usuario->nome  = strip_tags($dados['novoNome']);
        $usuario->email  = $dados['novoEmail'];
        $usuario->senha  = password_hash($dados['novaSenha'], PASSWORD_DEFAULT);
        $usuario->save();

        return $response->withJson('Usuário cadastrado com sucesso!');
        
    }

}
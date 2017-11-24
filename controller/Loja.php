<?php

namespace Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use  Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator;
use Helpers\Email;


class Loja {

	private $c = null;
    
    public function __construct($container)
    {
        $this->c = $container;
    }
    
    public function index(Request $request, Response $response) {
    	$produtos = \Model\Produto::
    		orderBy('titulo')->
    	get();
        
        $this->c['view']->render($response, 'loja.html', [
        	'produtos' => $produtos
        ]);
    }

    public function finalizar(Request $request, Response $response) {
        $dados = $request->getParsedBody();

        if(!Validator::length(8)->numeric()->validate(strip_tags($dados['cep']))) {
            return $response->withJson(
                [
                    'pedidoCep' => 'CEP inválido!'
            ], 400);
        }

        if(!Validator::length(3,100)->validate(strip_tags($dados['rua']))) {
            return $response->withJson(
                [
                    'pedidoRua' => 'O campo Rua contém um valor inválido!'
            ], 400);
        }

        if(!Validator::length(1, 5)->numeric()->validate(strip_tags($dados['numero']))) {
            return $response->withJson(
                [
                    'pedidoNumero' => 'O campo Nº contém um valor inválido!'
            ], 400);
        }
        
        if(!Validator::length(0,30)->validate(strip_tags($dados['complemento']))) {
            return $response->withJson(
                [
                    'pedidoComplemento' => 'O campo Complemento contém um valor inválido!'
            ], 400);
        }

        if(!Validator::length(3,100)->validate(strip_tags($dados['cidade']))) {
            return $response->withJson(
                [
                    'pedidoCidade' => 'O campo Complemento contém um valor inválido!'
            ], 400);
        }

        if(!Validator::length(2)->validate(strip_tags($dados['uf']))) {
            return $response->withJson(
                [
                    'pedidoUf' => 'O campo UF contém um valor inválido!'
            ], 400);
        }

        if(!Validator::length(16)->validate(strip_tags($dados['cartao']))) {
            return $response->withJson(
                [
                    'pedidoCartao' => 'O campo Cartão contém um valor inválido!'
            ], 400);
        }

        if(!Validator::length(11)->validate(strip_tags($dados['cpf']))) {
            return $response->withJson(
                [
                    'pedidoCpf' => 'O campo CPF contém um valor inválido!'
            ], 400);
        }

        if(!Validator::length(3)->validate(strip_tags($dados['codigoSegunranca']))) {
            return $response->withJson(
                [
                    'pedidoCodSeguranca' => 'O campo Cód. de segurança contém um valor inválido!'
            ], 400);
        }

        //valida o itens do pedido
        $codigos = []; 
        foreach($dados['pedido'] as $produto) {
            $codigos[$produto['id']] = $produto['id'];
        }

        $produtos = \Model\Produto::
            whereIn('id', $codigos)->
            select(DB::raw('COUNT(*) as total'))->
        get();

        if(count($codigos) != $produtos[0]->total) {
            return $response->withJson([
                'erro' => 'Seu pedido contem produtos inválidos!'
            ], 400);
        }
        
        //envia o e-mail
        $email = new Email($this->c);
        $email->Subject = 'AMIGO X notificações!';
        $email->Body = 'Seu pedido esta em fase de analise!';
        $email->AddAddress($_SESSION['email']);

        if(!$email->Send()) { 
            return $response->withJson([
                'erro' => 'Ocorreu um erro inesperado!'
            ], 400);
        } 

        return $response->withJson('Seu pedido foi finalizado ele esta em fase de analise!');
    }
}
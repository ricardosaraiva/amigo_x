<?php

namespace Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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
}
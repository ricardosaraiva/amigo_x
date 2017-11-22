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

        $this->c['view']->render($response, 'sessao.html');
    }
}
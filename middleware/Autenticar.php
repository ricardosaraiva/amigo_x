<?php
namespace Middleware;

class Autenticar {

    public function __invoke($request, $response, $next)
    {
        
        $id = isset( $_SESSION['id']) ? (int)  $_SESSION['id'] : 0;
        $navegador = isset($_SESSION['navegador']) ? $_SESSION['navegador'] : '';
        
        if($navegador != $_SERVER['HTTP_USER_AGENT'] || $id <= 0) {
            echo 'teste de conteudo';
            header('location: /login');
            exit;
        }

        $response = $next($request, $response);

        return $response;
    }

}
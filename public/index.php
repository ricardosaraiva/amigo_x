<?php
session_start();

require("../vendor/autoload.php");
require("../config.php");

$app = new \Slim\App($config);

$container = $app->getContainer();

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['view']->render($response, '404.html', [])->withStatus(404);
    };
};

//registra o twig no container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../view/', [
        // 'cache' => '../view/cache/'
    ]);

    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    $id = isset( $_SESSION['id']) ? (bool)  $_SESSION['id'] : 0;
   
    $view->getEnvironment()->addGlobal('auth', $id);

    return $view;
};


//inicia o eloquent
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('settings')['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();


//adiciona as rotas 
require('../route.php');

$app->run();
<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->get('/example', function($request, $response){
    return $this->view->render($response, 'example.twig');
});

$app->get('/', function($request, $response){
    return $this->view->render($response, 'index.twig');
})->setName('home');

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});


<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\ChartController;
use App\Controllers\ProductController;

$app->get('/example', function($request, $response){
    return $this->view->render($response, 'example.twig');
});

$app->get('/', function($request, $response){
    return $this->view->render($response, 'index.twig');
})->setName('home');

$app->get('/chart/allocation-type', ChartController::class.':allocationType')->setName('chart.allocation.type');

$app->get('/products/import/form',ProductController::class.':importForm')->setName('products.import_form');

$app->post('/products/import/list',ProductController::class.':importList')->setName('products.import_list');

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});


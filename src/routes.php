<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\ChartController;
use App\Controllers\ProductController;
use App\Controllers\ProductTypeController;

$app->get('/', function($request, $response){
    return $this->view->render($response, 'index.twig');
})->setName('home');

$app->get('/chart/allocation-type', ChartController::class.':allocationType')->setName('chart.allocation.type');

$app->get('/products',ProductController::class.':index')->setName('products.index');
$app->get('/products/create',ProductController::class.':create')->setName('products.create');
$app->post('/products/store',ProductController::class.':store')->setName('products.store');
$app->get('/products/{id}/edit',ProductController::class.':edit')->setName('products.edit');
$app->post('/products/update',ProductController::class.':update')->setName('products.update');
$app->get('/products/{id}/delete',ProductController::class.':delete')->setName('products.delete');
$app->get('/products/import/form',ProductController::class.':importForm')->setName('products.import_form');
$app->post('/products/import/list',ProductController::class.':importList')->setName('products.import_list');
$app->post('/products/import',ProductController::class.':import')->setName('products.import');
$app->get('/products/verify-concordance',ProductController::class.':verifyConcordance')->setName('products.verify_concordance');

$app->get('/productTypes',ProductTypeController::class.':index')->setName('product_types.index');
$app->get('/productTypes/create',ProductTypeController::class.':create')->setName('product_types.create');
$app->post('/productTypes/store',ProductTypeController::class.':store')->setName('product_types.store');
$app->get('/productTypes/{id}/edit',ProductTypeController::class.':edit')->setName('product_types.edit');
$app->post('/productTypes/update',ProductTypeController::class.':update')->setName('product_types.update');
$app->get('/productTypes/{id}/delete',ProductTypeController::class.':delete')->setName('product_types.delete');



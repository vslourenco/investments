<?php
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($c) {
    $renderer = $c->get('settings')['renderer'];
    
    $view = new \Slim\Views\Twig($renderer["template_path"], [
        'cache' => $renderer["cache_path"],
        'debug' => $renderer["debug"],
        'auto_reload' => $renderer["auto_reload"]
    ]);

    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->addExtension(new \Twig\Extension\DebugExtension());

    return $view;
};

$container['db'] = function ($c){
    $settings = $c->get('settings')['db'];
    $database = new PDO("mysql:host=${settings['server']};dbname=${settings['database_name']}", $settings["username"], $settings["password"]);
    return $database;
};
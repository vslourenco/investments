<?php
session_start();

require '../vendor/autoload.php';
require '../src/settings.php';

$app = new \Slim\App(['settings' => $config]);

require '../src/dependencies.php';
require '../src/middleware.php';
require '../src/routes.php';
require '../src/helpers.php';

$app->run();
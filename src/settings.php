<?php

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['renderer'] = [
    'template_path' => __DIR__.'/../templates/',
    'cache_path' => false,
    'debug' => true,
    'auto_reload' => true
];

$config['db'] = [
    'database_type' => DB_CONNECTION,
    'database_name' => DB_DATABASE,
    'server' => DB_HOST,
    'username' => DB_USERNAME,
    'password' => DB_PASSWORD,
    'charset' => DB_CHARSET,
    'collate' => DB_COLLATE
];
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
    'database_type' => 'mysql',
    'database_name' => 'investment',
    'server' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8'
];
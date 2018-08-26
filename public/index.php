<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PmPhp\Route\Router;
use PmPhp\Application;

$router = new Router;
$router->setNamespace('PmPhp\\Controller')
    ->setRouting([
    '/' => ['Index'],
    '/info' => ['Index', 'info'],
    '/login' => ['Index', 'login'],
    '/error' => ['Index', 'error'],
]);

$app = new Application($router);
$app->getLogger()->debug('========== app start ==========');

$app->run();

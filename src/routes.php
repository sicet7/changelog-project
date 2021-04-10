<?php
/** @var \Slim\App $app */

use App\Controllers\HomeController;
use App\Controllers\LoginController;

$container = $app->getContainer();

$app->addRoutingMiddleware();
$app->addErrorMiddleware(str_contains($container->get('application.mode'), 'dev'),true,true);

$app->get('/', [HomeController::class, 'get'])->setName('home');
$app->get('/login', [LoginController::class, 'get'])->setName('login');
$app->get(LoginController::REDIRECT_PATH, [LoginController::class, 'redirect'])->setName('login.redirect');
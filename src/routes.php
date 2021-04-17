<?php
/** @var \Slim\App $app */

use App\Controllers\ChangeLogController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

$container = $app->getContainer();

$app->addRoutingMiddleware();
$app->addErrorMiddleware(str_contains($container->get('application.mode'), 'dev'),true,true);

$app->get('/', [HomeController::class, 'get'])->add(AuthMiddleware::class)->setName('home');
$app->get('/login', [AuthController::class, 'get'])->setName('login');
$app->get(AuthController::REDIRECT_PATH, [AuthController::class, 'redirect'])->setName('auth.redirect');
$app->get(AuthController::LOGOUT_PATH, [AuthController::class, 'logout'])->setName('auth.logout');
$app->get('/changelog', [ChangeLogController::class, 'get'])->add(AuthMiddleware::class)->setName('changelog');
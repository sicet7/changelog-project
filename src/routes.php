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
$app->get('/changelogs', [ChangeLogController::class, 'get'])->add(AuthMiddleware::class)->setName('changelogs');
$app->get('/changelogs/create', [ChangeLogController::class, 'create'])->add(AuthMiddleware::class)->setName('changelogs.create');
$app->post('/changelogs/save', [ChangeLogController::class, 'save'])->add(AuthMiddleware::class)->setName('changelogs.save');
$app->get('/changelogs/{id}', [ChangeLogController::class, 'get'])->add(AuthMiddleware::class)->setName('changelogs.id');
$app->get('/changelogs/{id}/edit', [ChangeLogController::class, 'edit'])->add(AuthMiddleware::class)->setName('changelogs.id.edit');
$app->get('/changelogs/{id}/delete', [ChangeLogController::class, 'delete'])->add(AuthMiddleware::class)->setName('changelogs.id.delete');
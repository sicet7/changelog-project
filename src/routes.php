<?php
/** @var \Slim\App $app */

use App\Controllers\HomeController;

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true,true,true);

$app->get('/', [HomeController::class, 'get']);
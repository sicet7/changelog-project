<?php

require_once __DIR__ . '/vendor/autoload.php';

$containerBuilder = new \DI\ContainerBuilder();

$containerBuilder->addDefinitions(__DIR__ . '/definitions.php');

$app = \DI\Bridge\Slim\Bridge::create($containerBuilder->build());

require_once __DIR__ . '/routes.php';

$app->run();
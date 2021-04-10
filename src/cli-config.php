<?php
require_once __DIR__ . '/vendor/autoload.php';

$container = (new \DI\ContainerBuilder())->addDefinitions(__DIR__ . '/definitions.php')->build();

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($container->get(\Doctrine\ORM\EntityManager::class));
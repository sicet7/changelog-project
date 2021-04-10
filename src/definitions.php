<?php

use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

return [
    'view.path' => __DIR__ . '/view',
    'view.cache.path' => __DIR__ . '/cache',
    LoaderInterface::class => function(ContainerInterface $container) {
        return new FilesystemLoader($container->get('view.path'));
    },
    Environment::class => function (LoaderInterface $loader, ContainerInterface $container) {

        $cachePath = $container->get('view.cache.path');

        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        return new Environment($loader, [
            'cache' => $cachePath,
        ]);
    }
];
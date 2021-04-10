<?php

use App\Helpers\Base64Helper;
use App\Helpers\CacheHelper;
use App\Helpers\LibHelper;
use App\Helpers\LogHelper;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use function DI\create;
use function DI\get;

return [
    LogHelper::KEY => __DIR__ . 'var/log/app.log',
    'view.path' => __DIR__ . '/view',
    'cache.path' => __DIR__ . '/cache',
    LoaderInterface::class => function(ContainerInterface $container) {
        return new FilesystemLoader($container->get('view.path'));
    },
    Environment::class => function (LoaderInterface $loader, ContainerInterface $container) {

        $cachePath = $container->get('cache.path');

        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        return new Environment($loader, [
            'cache' => $cachePath,
        ]);
    },
    Client::class => create(Client::class),
    LoggerInterface::class => create(LogHelper::class)
        ->constructor(get(ContainerInterface::class)),
    Base64Helper::class => create(Base64Helper::class),
    CacheHelper::class => create(CacheHelper::class)
        ->constructor(
            get(ContainerInterface::class),
            get(Base64Helper::class)
        ),
    LibHelper::class => create(LibHelper::class)
        ->constructor(
            get(Client::class),
            get(LoggerInterface::class),
            get(ContainerInterface::class),
            get(CacheHelper::class)
        ),
];
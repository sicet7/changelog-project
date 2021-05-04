<?php

use App\Database\Repositories\LogEntryRepository;
use App\Database\Repositories\LogRepository;
use App\Helpers\AuthHelper;
use App\Helpers\Base64Helper;
use App\Helpers\CacheHelper;
use App\Helpers\LogHelper;
use App\Helpers\RedirectHelper;
use App\Middleware\AuthMiddleware;
use App\Twig\FilterContainer;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use GuzzleHttp\Client;
use League\CommonMark\CommonMarkConverter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use function DI\create;
use function DI\env;
use function DI\get;

return [
    'azuread.openid.client_id' => env('CLIENT_ID', ''),
    'azuread.openid.tenant_id' => env('TENANT_ID', ''),
    'azuread.openid.client_secret' => env('CLIENT_SECRET', ''),
    'application.mode' => 'development',
    LogHelper::KEY => __DIR__ . '/var/log/app.log',
    'view.path' => __DIR__ . '/view',
    'cache.path' => __DIR__ . '/cache',
    'vendor.path' => __DIR__ . '/vendor',
    'locale' => [
        'timezone' => 'Europe/Copenhagen',
        'format' => 'd-m-Y H:i:s',
    ],
    'http.libs' => [
        'js' => [
            '/js/jquery.min.js',
            '/js/flatpickr.js',
            '/js/bootstrap.bundle.min.js',
            '/js/sweetalert2.min.js',
        ],
        'css' => [
            '/css/flatpickr.min.css',
            '/css/dark.css',
            '/css/bootstrap.min.css',
            '/css/font-awesome.min.css',
            '/css/sweetalert2.min.css',
        ],
    ],
    'markdown.options' => [
        'html_input' => 'allow',
        'allow_unsafe_links' => true,
        'max_nesting_level' => INF,
    ],
    'database.connection.url' => env('DATABASE_URL'),
    'database.entity.paths' => [
        __DIR__ . '/app/Database/Entities/',
    ],
    'database.proxies.dir' => __DIR__ . '/cache/Database/Proxies',
    'database.proxies.namespace' => 'Database\Proxies',
    'database.migrations.config' => [
        'table_storage' => [
            'table_name' => 'doctrine_migration_versions',
            'version_column_name' => 'version',
            'version_column_length' => 1024,
            'executed_at_column_name' => 'executed_at',
            'execution_time_column_name' => 'execution_time',
        ],

        'migrations_paths' => [
            'App\Database\Migrations' => __DIR__ . '/app/Database/Migrations',
        ],

        'all_or_nothing' => true,
        'check_database_platform' => true,
        'organize_migrations' => 'none',
    ],


    LoaderInterface::class => function(ContainerInterface $container) {
        return new FilesystemLoader($container->get('view.path'));
    },
    Environment::class => function (
        LoaderInterface $loader,
        ContainerInterface $container,
        FilterContainer $filterContainer
    ) {

        $cachePath = $container->get('cache.path');

        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        $options = [
            'cache' => $cachePath,
        ];
        if (str_contains($container->get('application.mode'), 'dev')) {
            $options = [];
        }

        $env = new Environment($loader, $options);
        foreach ($filterContainer->getMap() as $filter) {
            $env->addFilter($filter);
        }
        return $env;
    },
    FilterContainer::class => create(FilterContainer::class)
        ->constructor(get(ContainerInterface::class)),
    Client::class => create(Client::class),
    LoggerInterface::class => create(LogHelper::class)
        ->constructor(get(ContainerInterface::class)),
    Base64Helper::class => create(Base64Helper::class),
    RedirectHelper::class => create(RedirectHelper::class)
        ->constructor(get(ResponseFactory::class)),
    CacheHelper::class => create(CacheHelper::class)
        ->constructor(
            get(ContainerInterface::class),
            get(Base64Helper::class)
        ),
    AuthHelper::class => create(AuthHelper::class)
        ->constructor(
            get(Base64Helper::class),
            get(ContainerInterface::class),
            get(CacheHelper::class),
            get(LoggerInterface::class)
        ),
    AuthMiddleware::class => create(AuthMiddleware::class)->constructor(
        get(AuthHelper::class),
        get(RedirectHelper::class)
    ),
    MappingDriver::class => function (ContainerInterface $container) {
        return new StaticPHPDriver($container->get('database.entity.paths'));
    },
    Configuration::class => function(ContainerInterface $container, MappingDriver $mappingDriver) {
        $appMode = $container->get('application.mode');
        $config = new Configuration();

        // TODO: Make better cache solution as this is only recommended for development.
        $cache = new \Doctrine\Common\Cache\ArrayCache;
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);

        $config->setMetadataDriverImpl($mappingDriver);
        $config->setProxyDir($container->get('database.proxies.dir'));
        $config->setProxyNamespace($container->get('database.proxies.namespace'));

        if (str_contains($appMode, 'dev')) {
            $config->setAutoGenerateProxyClasses(true);
        } else {
            $config->setAutoGenerateProxyClasses(false);
        }
        return $config;
    },
    EntityManager::class => function(ContainerInterface $container, Configuration $configuration) {
        return EntityManager::create([
            'url' => $container->get('database.connection.url'),
        ], $configuration);
    },
    LogRepository::class => create(LogRepository::class)
        ->constructor(get(EntityManager::class)),
    CommonMarkConverter::class => create(CommonMarkConverter::class)
        ->constructor(get('markdown.options')),
];

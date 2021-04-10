<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LibHelper
{
    public const JQUERY_SRC = 'https://code.jquery.com/jquery-3.6.0.min.js';

    public const JQUERY_CACHE_KEY = 'jquery';

    /**
     * @var Client
     */
    private Client $httpClient;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var CacheHelper
     */
    private CacheHelper $cacheHelper;

    public function __construct(
        Client $httpClient,
        LoggerInterface $logger,
        ContainerInterface $container,
        CacheHelper $cacheHelper
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->container = $container;
        $this->cacheHelper = $cacheHelper;
    }

    public function getJquery()
    {
        try {

            if ($this->cacheHelper->contains(self::JQUERY_CACHE_KEY)) {
                return $this->cacheHelper->read(self::JQUERY_CACHE_KEY, false) ?? '';
            }

            $response = $this->httpClient->get(self::JQUERY_SRC);

            $content = '';
            if ($response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                $this->cacheHelper->save(self::JQUERY_CACHE_KEY, $content, false);
            }
            return $content;
        } catch (\Exception $exception) {
            $this->logger->error($exception);
            return '';
        }
    }

    /**
     * @return string
     */
    public function getBootstrapJs()
    {
        $vendorPath = $this->container->get('vendor.path');
        return file_get_contents($vendorPath . '/twbs/bootstrap/dist/js/bootstrap.bundle.min.js') ?? '';
    }

    /**
     * @return string
     */
    public function getBootstrapCss()
    {
        $vendorPath = $this->container->get('vendor.path');
        return file_get_contents($vendorPath . '/twbs/bootstrap/dist/css/bootstrap.min.css') ?? '';
    }

}
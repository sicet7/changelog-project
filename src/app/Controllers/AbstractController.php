<?php

namespace App\Controllers;

use App\Helpers\ContextHelper;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment;

abstract class AbstractController
{
    public const TEMPLATE = 'error.twig';

    /**
     * @var Environment
     */
    private Environment $twig;
    /**
     * @var ContextHelper
     */
    private ContextHelper $contextHelper;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * AbstractController constructor.
     * @param Environment $twig
     * @param ContextHelper $contextHelper
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Environment $twig,
        ContextHelper $contextHelper,
        ResponseFactory $responseFactory
    ) {
        $this->twig = $twig;
        $this->contextHelper = $contextHelper;
        $this->responseFactory = $responseFactory;
    }

    public function render(array $context = [])
    {
        return $this->twig->render(static::TEMPLATE, $this->contextHelper->makeContext($context));
    }

    /**
     * @param array $context
     * @return ResponseInterface
     */
    public function renderResponse(array $context = [])
    {
        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write($this->render($context));
        return $response;
    }
}
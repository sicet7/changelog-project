<?php

namespace App\Controllers;

use App\Helpers\ContextHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment;

abstract class AbstractController
{
    protected string $template = 'error.twig';

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
        return $this->twig->render($this->template, $this->contextHelper->makeContext($context));
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

    /**
     * @param Request $request
     * @return bool
     */
    public function isAjaxRequest(Request $request): bool
    {
        return $request->hasHeader('X-Requested-With') && (
                (
                    is_string($request->getHeader('X-Requested-With')) &&
                    strtolower($request->getHeader('X-Requested-With')) == 'xmlhttprequest'
                ) || (
                    is_array($request->getHeader('X-Requested-With')) &&
                    in_array('xmlhttprequest', array_map('strtolower', $request->getHeader('X-Requested-With')))
                )
            );
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function getReferer(Request $request): ?string
    {
        if (!$request->hasHeader('Referer')) {
            return null;
        }
        $ref = $request->getHeader('Referer');
        if (is_array($ref)) {
            $ref = $ref[array_keys($ref)[0]];
        }
        return $ref;
    }
}
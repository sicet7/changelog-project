<?php

namespace App\Middleware;

use App\Helpers\AuthHelper;
use App\Helpers\RedirectHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

class AuthMiddleware extends Middleware
{
    /**
     * @var AuthHelper
     */
    private AuthHelper $authHelper;

    /**
     * @var RedirectHelper
     */
    private RedirectHelper $redirectHelper;

    /**
     * AuthMiddleware constructor.
     * @param AuthHelper $authHelper
     * @param RedirectHelper $redirectHelper
     */
    public function __construct(
        AuthHelper $authHelper,
        RedirectHelper $redirectHelper
    ) {
        $this->authHelper = $authHelper;
        $this->redirectHelper = $redirectHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->authHelper->isAuthenticated()) {
            return $this->redirectHelper->tmp( '/login');
        }
        return $handler->handle($request);
    }
}
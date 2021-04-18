<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\ContextHelper;
use App\Helpers\RedirectHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment;

class AuthController extends AbstractController
{
    public const REDIRECT_PATH = '/auth/redirect';
    public const LOGOUT_PATH = '/auth/logout';

    /**
     * @var AuthHelper
     */
    private AuthHelper $authHelper;

    /**
     * @var RedirectHelper
     */
    private RedirectHelper $redirectHelper;

    public function __construct(
        Environment $twig,
        ContextHelper $contextHelper,
        ResponseFactory $responseFactory,
        AuthHelper $authHelper,
        RedirectHelper $redirectHelper
    ) {
        parent::__construct($twig, $contextHelper, $responseFactory);
        $this->authHelper = $authHelper;
        $this->redirectHelper = $redirectHelper;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function get(Request $request, Response $response)
    {
        $this->template = 'pages/login.twig';
        if ($this->authHelper->isAuthenticated()) {
            return $this->redirectHelper->tmp($response, '/');
        }
        return $this->renderResponse([
            'login' => [
                'url' => $this->authHelper->getLoginUrl()
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function redirect(Request $request, Response $response)
    {
        if ($this->authHelper->authenticate($request)) {
            return $this->redirectHelper->tmp($response, '/');
        }
        return $this->redirectHelper->tmp($response, '/login');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function logout(Request $request, Response $response)
    {
        $this->authHelper->logout();
        return $this->redirectHelper->tmp($response, '/login');
    }
}
<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\ContextHelper;
use App\Helpers\RedirectHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class LoginController extends AbstractController
{
    public const REDIRECT_PATH = '/login/redirect';
    public const TEMPLATE = 'login.twig';

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
        AuthHelper $authHelper,
        RedirectHelper $redirectHelper
    ) {
        parent::__construct($twig, $contextHelper);
        $this->authHelper = $authHelper;
        $this->redirectHelper = $redirectHelper;
    }

    public function get(Request $request, Response $response)
    {
        $response->getBody()->write($this->render([
            'login' => [
                'url' => $this->authHelper->getLoginUrl()
            ]
        ]));
        return $response;
    }

    public function redirect(Request $request, Response $response)
    {
        if ($this->authHelper->authenticate($request)) {
            return $this->redirectHelper->tmp($response, '/dashboard');
        }
        return $this->redirectHelper->tmp($response, '/login');
    }
}
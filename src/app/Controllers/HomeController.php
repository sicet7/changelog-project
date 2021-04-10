<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\ContextHelper;
use App\Helpers\RedirectHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class HomeController
{
    /**
     * @var RedirectHelper
     */
    private RedirectHelper $redirectHelper;

    /**
     * @var AuthHelper
     */
    private AuthHelper $authHelper;

    /**
     * HomeController constructor.
     * @param RedirectHelper $redirectHelper
     * @param AuthHelper $authHelper
     */
    public function __construct(
        RedirectHelper $redirectHelper,
        AuthHelper $authHelper
    )
    {
        $this->redirectHelper = $redirectHelper;
        $this->authHelper = $authHelper;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function get(Request $request, Response $response)
    {
        if ($this->authHelper->isAuthenticated()) {
            return $this->redirectHelper->tmp($response, '/dashboard');
        }
        return $this->redirectHelper->tmp($response, '/login');
    }
}
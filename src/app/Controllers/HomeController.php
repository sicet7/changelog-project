<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\ContextHelper;
use App\Helpers\RedirectHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment;

class HomeController extends AbstractController
{
    public const TEMPLATE = 'pages/toolbox.twig';

    /**
     * @var RedirectHelper
     */
    private RedirectHelper $redirectHelper;

    /**
     * @var AuthHelper
     */
    private AuthHelper $authHelper;

    public function __construct(
        Environment $twig,
        ContextHelper $contextHelper,
        ResponseFactory $responseFactory,
        RedirectHelper $redirectHelper,
        AuthHelper $authHelper
    ) {
        parent::__construct($twig, $contextHelper, $responseFactory);
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
        if (!$this->authHelper->isAuthenticated()) {
            return $this->redirectHelper->tmp($response, '/login');
        }
        return $this->renderResponse([
            'navbar' => [
                'title' => 'Toolbox'
            ]
        ]);
    }
}
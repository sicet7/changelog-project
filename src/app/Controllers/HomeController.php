<?php

namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController extends AbstractController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function get(Request $request, Response $response)
    {
        $this->template = 'pages/toolbox.twig';
        return $this->renderResponse([
            'navbar' => [
                'title' => 'Toolbox'
            ],
            'page' => [
                'title' => 'Toolbox'
            ],
        ]);
    }
}
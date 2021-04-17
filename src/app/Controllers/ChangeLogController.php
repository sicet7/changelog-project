<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ChangeLogController extends AbstractController
{
    public const TEMPLATE = 'pages/changelog.twig';

    public function get(Request $request, Response $response)
    {
        return $this->renderResponse([
            'navbar' => [
                'title' => 'Selected'
            ]
        ]);
    }
}
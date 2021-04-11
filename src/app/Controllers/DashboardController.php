<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends AbstractController
{
    public const TEMPLATE = 'dashboard.twig';

    public function get(Request $request, Response $response)
    {
        $response->getBody()->write($this->render());
        return $response;
    }
}
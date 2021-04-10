<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginController extends AbstractController
{
    public const REDIRECT_PATH = '/login/redirect';
    public const TEMPLATE = 'login.twig';

    public function get(Request $request, Response $response)
    {
        $response->getBody()->write($this->render());
        return $response;
    }

    public function redirect(Request $request, Response $response)
    {
        $response->getBody()->write('redirect');
        return $response;
    }
}
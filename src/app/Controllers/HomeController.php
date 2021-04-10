<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class HomeController
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function get(Request $request, Response $response)
    {
        $html = 'oiejrgoijerg';

        $response->getBody()->write($this->twig->render('variable.twig', ['variable' => $html]));
        return $response;
    }
}
<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;

class RedirectHelper
{
    public function tmp(Response $response, string $location): Response
    {
        return $response->withStatus(302)->withHeader('Location', $location);
    }

    public function perm(Response $response, string $location): Response
    {
        return $response->withStatus(301)->withHeader('Location', $location);
    }
}
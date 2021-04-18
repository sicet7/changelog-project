<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;

class RedirectHelper
{
    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function tmp(string $location): Response
    {
        $response = $this->responseFactory->createResponse(302);
        return $response->withHeader('Location', $location);
    }

    public function perm(string $location): Response
    {
        $response = $this->responseFactory->createResponse(301);
        return $response->withHeader('Location', $location);
    }
}
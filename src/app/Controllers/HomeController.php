<?php

namespace App\Controllers;

use App\Helpers\ContextHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class HomeController
{
    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * @var ContextHelper
     */
    private ContextHelper $contextHelper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * HomeController constructor.
     * @param LoggerInterface $logger
     * @param Environment $twig
     * @param ContextHelper $contextHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Environment $twig,
        ContextHelper $contextHelper
    )
    {
        $this->twig = $twig;
        $this->contextHelper = $contextHelper;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function get(Request $request, Response $response)
    {
        try {
            $html = date('c');

            $context = $this->contextHelper->makeContext([
                'variable' => $html,
                'title' => 'Page title test'
            ]);

            $response->getBody()->write($this->twig->render('variable.twig', $context));
            return $response;
        } catch (\Exception $exception) {
            $this->logger->error($exception);
            $response->withStatus(502, 'An Error occurred, please check the logs for more information.');
            $response->getBody()->write('An Error occurred, please check the logs for more information.');
        }
    }
}
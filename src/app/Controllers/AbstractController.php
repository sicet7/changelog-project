<?php

namespace App\Controllers;

use App\Helpers\ContextHelper;
use Twig\Environment;

abstract class AbstractController
{
    public const TEMPLATE = 'error.twig';

    /**
     * @var Environment
     */
    private Environment $twig;
    /**
     * @var ContextHelper
     */
    private ContextHelper $contextHelper;

    /**
     * AbstractController constructor.
     * @param Environment $twig
     * @param ContextHelper $contextHelper
     */
    public function __construct(Environment $twig, ContextHelper $contextHelper)
    {
        $this->twig = $twig;
        $this->contextHelper = $contextHelper;
    }

    public function render(array $context = [])
    {
        return $this->twig->render(static::TEMPLATE, $this->contextHelper->makeContext($context));
    }
}
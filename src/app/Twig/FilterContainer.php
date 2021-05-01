<?php

namespace App\Twig;

use App\Helpers\TimeHelper;
use App\Twig\Filters\MarkdownFilter;
use Psr\Container\ContainerInterface;
use Twig\TwigFilter;

class FilterContainer
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return TwigFilter[]
     */
    public function getMap(): array
    {
        return [
            new TwigFilter(
                'markdown',
                [$this->container->get(MarkdownFilter::class), 'execute'],
                MarkdownFilter::getOptions()
            ),
            new TwigFilter('convertTimezone', [TimeHelper::class, 'convertTimezone']),
        ];
    }
}
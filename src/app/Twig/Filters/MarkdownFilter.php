<?php

namespace App\Twig\Filters;

use League\CommonMark\CommonMarkConverter;

class MarkdownFilter
{
    /**
     * @var CommonMarkConverter
     */
    private CommonMarkConverter $converter;

    /**
     * MarkdownFilter constructor.
     * @param CommonMarkConverter $converter
     */
    public function __construct(CommonMarkConverter $converter)
    {
        $this->converter = $converter;
    }

    public static function getOptions(): array
    {
        return [
            'is_safe' => ['html']
        ];
    }

    public function execute(string $content)
    {
        return $this->converter->convertToHtml($content);
    }
}
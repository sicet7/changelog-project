<?php

namespace App\Helpers;

class ContextHelper
{
    /**
     * @var LibHelper
     */
    private LibHelper $libHelper;

    /**
     * ContextHelper constructor.
     * @param LibHelper $libHelper
     */
    public function __construct(LibHelper $libHelper)
    {
        $this->libHelper = $libHelper;
    }

    public function getLibs(): array
    {
        return [
            'lib' => [
                'jquery' => $this->libHelper->getJquery()
            ]
        ];
    }

    public function makeContext(array $context): array
    {
        return array_replace_recursive($this->getLibs(), $context);
    }

}
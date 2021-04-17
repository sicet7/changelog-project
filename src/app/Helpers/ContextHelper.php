<?php

namespace App\Helpers;

class ContextHelper
{
    /**
     * @var LibHelper
     */
    private LibHelper $libHelper;

    /**
     * @var AuthHelper
     */
    private AuthHelper $authHelper;

    /**
     * ContextHelper constructor.
     * @param LibHelper $libHelper
     * @param AuthHelper $authHelper
     */
    public function __construct(
        LibHelper $libHelper,
        AuthHelper $authHelper
    ) {
        $this->libHelper = $libHelper;
        $this->authHelper = $authHelper;
    }

    protected function getArray(): array
    {
        return [
            'lib' => [
                'jquery' => $this->libHelper->getJquery(),
                'bootstrap' => [
                    'js' => $this->libHelper->getBootstrapJs(),
                    'css' => $this->libHelper->getBootstrapCss(),
                ]
            ],
            'auth' => [
                'login' => [
                    'url' => $this->authHelper->getLoginUrl(),
                ],
                'logout' => [
                    'url' => $this->authHelper->getLogoutUrl(),
                ]
            ]
        ];
    }

    public function makeContext(array $context): array
    {
        return array_replace_recursive($this->getArray(), $context);
    }
}
<?php

namespace App\Helpers;

use Psr\Container\ContainerInterface;

class ContextHelper
{

    /**
     * @var AuthHelper
     */
    private AuthHelper $authHelper;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * ContextHelper constructor.
     * @param AuthHelper $authHelper
     * @param ContainerInterface $container
     */
    public function __construct(
        AuthHelper $authHelper,
        ContainerInterface $container
    ) {
        $this->authHelper = $authHelper;
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getArray(): array
    {
        return [
            'lib' => $this->container->get('http.libs'),
            'auth' => [
                'login' => [
                    'url' => $this->authHelper->getLoginUrl(),
                ],
                'logout' => [
                    'url' => $this->authHelper->getLogoutUrl(),
                ],
                'claims' => $this->authClaims(),
            ]
        ];
    }

    /**
     * @return array|null
     */
    protected function authClaims(): ?array
    {
        $token = $this->authHelper->getToken();
        if ($token === null) {
            return null;
        }
        return $token->getIdToken()->getClaims();
    }

    /**
     * @param array $context
     * @return array
     */
    public function makeContext(array $context): array
    {
        return array_replace_recursive($this->getArray(), $context);
    }
}
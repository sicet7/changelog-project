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
     * @var MessageHelper
     */
    private MessageHelper $messageHelper;

    /**
     * @var ResourceHelper
     */
    private ResourceHelper $resourceHelper;

    /**
     * ContextHelper constructor.
     * @param AuthHelper $authHelper
     * @param ContainerInterface $container
     * @param MessageHelper $messageHelper
     * @param ResourceHelper $resourceHelper
     */
    public function __construct(
        AuthHelper $authHelper,
        ContainerInterface $container,
        MessageHelper $messageHelper,
        ResourceHelper $resourceHelper
    ) {
        $this->authHelper = $authHelper;
        $this->container = $container;
        $this->messageHelper = $messageHelper;
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * @param bool $uri
     * @param bool $query
     * @return string
     */
    protected function getCurrentPageLink(bool $uri = true, bool $query = false): string
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") .
            $_SERVER['HTTP_HOST'];
        if ($uri) {
            $url .= $_SERVER['DOCUMENT_URI'];
        }
        if ($query && !empty($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }
        return $url;
    }

    /**
     * @return array
     */
    protected function getArray(): array
    {
        return [
            'page' => [
                'link' => $this->getCurrentPageLink(),
            ],
            'lib' => $this->container->get('http.libs'),
            'auth' => [
                'login' => [
                    'url' => $this->authHelper->getLoginUrl(),
                ],
                'logout' => [
                    'url' => $this->authHelper->getLogoutUrl(),
                ],
                'claims' => $this->authClaims(),
            ],
            'msg' => $this->getMessages(),
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
     * @return array
     */
    protected function getMessages(): array
    {
        $messages = $this->messageHelper->getAllMessages();
        $this->messageHelper->reset();
        return $messages;
    }

    /**
     * @param array $context
     * @return array
     */
    public function makeContext(array $context): array
    {
        return array_replace_recursive($this->getArray(), $this->resourceHelper->getLoadedArray(), $context);
    }
}
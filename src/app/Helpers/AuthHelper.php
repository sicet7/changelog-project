<?php

namespace App\Helpers;

use App\Controllers\LoginController;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

class AuthHelper
{

    public const EXPECTED_ALG = 'RS256';
    public const BASE_URL = 'https://login.microsoftonline.com/';
    public const AUTH_PATH = '/oauth2/v2.0/authorize';
    public const TOKEN_PATH = '/oauth2/v2.0/token';
    public const INFO_PATH = '/common/v2.0/.well-known/openid-configuration';

    /**
     * @var Base64Helper
     */
    private Base64Helper $base64Helper;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var string|null
     */
    private ?string $loginUrl = null;

    /**
     * AuthHelper constructor.
     * @param Base64Helper $base64Helper
     * @param ContainerInterface $container
     */
    public function __construct(Base64Helper $base64Helper, ContainerInterface $container)
    {
        $this->base64Helper = $base64Helper;
        $this->client = new Client(['base_uri' => self::BASE_URL]);
        $this->container = $container;
    }

    public function getTenantId()
    {
        return $this->container->get('azuread.openid.tenant_id');
    }

    public function getClientId()
    {
        return $this->container->get('azuread.openid.client_id');
    }

    public function getLoginUrl(): string
    {
        if ($this->loginUrl === null) {
            $_SESSION['state'] = $state = $this->generateState();
            $this->loginUrl = self::BASE_URL . $this->getTenantId() . self::AUTH_PATH . '?' .
                'client_id=' . $this->getClientId() . '&' .
                'response_type=code&' .
                'redirect_uri=' . urlencode($this->getRedirectUrl()) . '&' .
                'scope=openid&' .
                'state=' . $state;
        }
        return $this->loginUrl;
    }

    public function verifyState(string $state): bool
    {
        if (!isset($_SESSION['state']) || empty($state)) {
            return false;
        }
        return $_SESSION['state'] == $state;
    }

    public function generateState(): string
    {
        return $this->base64Helper->urlsafeEncode(random_bytes(32));
    }

    public function getRedirectUrl(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") .
            $_SERVER['HTTP_HOST'] .
            LoginController::REDIRECT_PATH;
    }

    public function isAuthenticated(): bool
    {
        //TODO: Implement this
        return false;
    }

}
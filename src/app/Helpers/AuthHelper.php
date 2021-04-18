<?php

namespace App\Helpers;

use App\Controllers\AuthController;
use App\Data\Token;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SimpleJWT\JWT;
use SimpleJWT\Keys\KeySet;
use SimpleJWT\Keys\RSAKey;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthHelper
{

    public const EXPECTED_ALG = 'RS256';
    public const BASE_URL = 'https://login.microsoftonline.com/';
    public const AUTH_PATH = '/oauth2/v2.0/authorize';
    public const TOKEN_PATH = '/oauth2/v2.0/token';
    public const INFO_PATH = '/common/v2.0/.well-known/openid-configuration';
    public const USER_INFO_PATH = 'https://graph.microsoft.com/oidc/userinfo';

    private const ACCESS_TOKEN_SESSION_KEY = 'access_token';
    private const ID_TOKEN_SESSION_KEY = 'id_token';
    private const STATE_TOKEN_SESSION_KEY = 'state';

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
     * @var CacheHelper
     */
    private CacheHelper $cacheHelper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Token|null
     */
    private ?Token $token = null;

    /**
     * AuthHelper constructor.
     * @param Base64Helper $base64Helper
     * @param ContainerInterface $container
     * @param CacheHelper $cacheHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Base64Helper $base64Helper,
        ContainerInterface $container,
        CacheHelper $cacheHelper,
        LoggerInterface $logger
    ) {
        $this->base64Helper = $base64Helper;
        $this->client = new Client(['base_uri' => self::BASE_URL]);
        $this->container = $container;
        $this->cacheHelper = $cacheHelper;
        $this->logger = $logger;
    }

    protected function getTenantId()
    {
        return $this->container->get('azuread.openid.tenant_id');
    }

    protected function getClientId()
    {
        return $this->container->get('azuread.openid.client_id');
    }

    protected function getClientSecret()
    {
        return $this->container->get('azuread.openid.client_secret');
    }

    public function getLoginUrl(): string
    {
        if ($this->loginUrl === null) {
            $_SESSION[self::STATE_TOKEN_SESSION_KEY] = $state = $this->generateState();
            $this->loginUrl = self::BASE_URL . $this->getTenantId() . self::AUTH_PATH . '?' .
                'client_id=' . $this->getClientId() . '&' .
                'response_type=code&' .
                'redirect_uri=' . urlencode($this->getRedirectUrl()) . '&' .
                'scope=openid&' .
                'state=' . $state;
        }
        return $this->loginUrl;
    }

    /**
     * @return string
     */
    public function getLogoutUrl(): string
    {
        return AuthController::LOGOUT_PATH;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function generateState(): string
    {
        return $this->base64Helper->urlsafeEncode(random_bytes(32));
    }

    /**
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") .
            $_SERVER['HTTP_HOST'] .
            AuthController::REDIRECT_PATH;
    }

    /**
     * @return KeySet|null
     */
    protected function getJWTKeySet(): ?KeySet
    {
        try {
            $date = date('y-m-d');
            $infoData = $this->getInfoRequestData();
            if (empty($infoData) ||
                !is_array($infoData) ||
                !array_key_exists('jwks_uri', $infoData) ||
                !is_string($infoData['jwks_uri'])
            ) {
                return null;
            }
            $jwksUri = $infoData['jwks_uri'];
            $loaded = false;
            if ($this->cacheHelper->contains($jwksUri. $date)) {
                $content = $this->cacheHelper->read($jwksUri. $date, false);
                $loaded = true;
            } else {
                $client = $this->container->get(Client::class);
                $keysResponse = $client->get($jwksUri);
                if ($keysResponse->getStatusCode() != 200) {
                    return null;
                }
                $content = $keysResponse->getBody()->getContents();
            }

            $jsonData = json_decode($content, true);
            if (json_last_error() != JSON_ERROR_NONE ||
                !isset($jsonData['keys']) ||
                !is_array($jsonData['keys'])
            ) {
                return null;
            }

            if (!$loaded) {
                $this->cacheHelper->save($jwksUri. $date, $content, false);
            }

            $keyset = new KeySet();
            foreach ($jsonData['keys'] as $jwkKey) {
                $keyset->add(new RSAKey($jwkKey, 'php'));
            }
            return $keyset;
        } catch (\Exception | \Throwable $exception) {
            $this->logger->error($exception);
            return null;
        }
    }

    /**
     * @return mixed
     */
    protected function getInfoRequestData()
    {
        try {
            $key = self::INFO_PATH . date('y-m-d');
            $loaded = false;
            if ($this->cacheHelper->contains($key)) {
                $content = $this->cacheHelper->read($key, false);
                $loaded = true;
            }
            if (!isset($content)) {
                $response = $this->client->get(self::INFO_PATH, [
                    'query' => [
                        'appid' => $this->getClientId(),
                    ]
                ]);
                if ($response->getStatusCode() != 200) {
                    return false;
                }
                $content = $response->getBody()->getContents();
            }
            $jsonData = json_decode($content, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                return false;
            }
            if (!empty($content) && !$loaded) {
                $this->cacheHelper->save($key, $content, false);
            }
            return $jsonData;
        } catch (\Exception | \Throwable $exception) {
            $this->logger->error($exception);
            return false;
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function authenticate(Request $request): bool
    {
        try {
            $queryParams = $request->getQueryParams();

            if (!isset($_SESSION[self::STATE_TOKEN_SESSION_KEY]) ||
                !isset($queryParams['state']) ||
                empty($queryParams['state']) ||
                $queryParams['state'] != $_SESSION[self::STATE_TOKEN_SESSION_KEY] ||
                !isset($queryParams['code']) ||
                !is_string($queryParams['code'])
            ) {
                return false;
            }

            $code = $queryParams['code'];
            $redirectUri = $this->getRedirectUrl();

            $response = $this->client->request(
                'POST',
                $this->getTenantId() . self::TOKEN_PATH,
                [
                    'form_params' => [
                        'client_id' => $this->getClientId(),
                        'scope' => 'openid',
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => $redirectUri,
                        'client_secret' => $this->getClientSecret(),
                    ]
                ]
            );

            if ($response->getStatusCode() != 200) {
                return false;
            }

            $jsonData = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() != JSON_ERROR_NONE ||
                !isset($jsonData['id_token']) ||
                !is_string($jsonData['id_token']) ||
                !isset($jsonData['access_token']) ||
                !is_string($jsonData['access_token'])
            ) {
                return false;
            }

            $keySet = $this->getJWTKeySet();
            if (empty($keySet)) {
                return false;
            }

            $jwt = JWT::decode($jsonData['id_token'], $keySet, self::EXPECTED_ALG);

            if ($this->token === null) {
                $this->token = new Token($jsonData['access_token'], $jwt);
            }
            $_SESSION[self::ACCESS_TOKEN_SESSION_KEY] = $jsonData['access_token'];
            $_SESSION[self::ID_TOKEN_SESSION_KEY] = $jsonData['id_token'];
            return true;
        } catch (\Exception | \Throwable $exception) {
            $this->logger->error($exception);
            return false;
        }
    }

    public function logout()
    {
        if (isset($_SESSION[self::ID_TOKEN_SESSION_KEY])) {
            unset($_SESSION[self::ID_TOKEN_SESSION_KEY]);
        }
        if (isset($_SESSION[self::ACCESS_TOKEN_SESSION_KEY])) {
            unset($_SESSION[self::ACCESS_TOKEN_SESSION_KEY]);
        }
        $this->token = null;
        return true;
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        try {
            if (!isset($_SESSION[self::ACCESS_TOKEN_SESSION_KEY]) ||
                !is_string($_SESSION[self::ACCESS_TOKEN_SESSION_KEY]) ||
                !isset($_SESSION[self::ID_TOKEN_SESSION_KEY]) ||
                !is_string($_SESSION[self::ID_TOKEN_SESSION_KEY])
            ) {
                return false;
            }

            $keySet = $this->getJWTKeySet();
            if (empty($keySet)) {
                return false;
            }

            $jwt = JWT::decode($_SESSION[self::ID_TOKEN_SESSION_KEY], $keySet, self::EXPECTED_ALG);

            if (!array_key_exists('email', $jwt->getClaims())) {
                $this->logger->error('Missing "email" Claim on ID Token.');
                return false;
            }

            if ($this->token === null) {
                $this->token = new Token($_SESSION[self::ACCESS_TOKEN_SESSION_KEY], $jwt);
            }
            return true;
        } catch (\Exception $exception) {
            $this->logger->error($exception);
            return false;
        }
    }

    /**
     * @return Token|null
     */
    public function getToken(): ?Token
    {
        return $this->token;
    }
}
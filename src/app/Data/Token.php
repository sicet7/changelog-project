<?php

namespace App\Data;

use SimpleJWT\JWT;

class Token
{

    /**
     * @var string
     */
    private string $accessToken;

    /**
     * @var JWT
     */
    private JWT $token;

    /**
     * Token constructor.
     * @param string $accessToken
     * @param JWT $token
     */
    public function __construct(
        string $accessToken,
        JWT $token
    ) {
        $this->accessToken = $accessToken;
        $this->token = $token;
    }

    /**
     * @return JWT
     */
    public function getIdToken(): JWT
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
<?php

namespace Overtrue\LaravelKeycloakAdmin;

use Illuminate\Support\Facades\Cache;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;
use Overtrue\Keycloak\OAuth\TokenStorageInterface;

class LaravelCacheTokenStorage implements TokenStorageInterface
{
    public function __construct(
        private readonly string $accessTokenCacheKey = 'laravel-keycloak-admin-cache-token',
        private readonly string $refreshTokenCacheKey = 'laravel-keycloak-admin-cache-refresh-token',
    ) {}

    public function storeAccessToken(Token $accessToken): void
    {
        Cache::put($this->accessTokenCacheKey, $accessToken->toString());
    }

    public function storeRefreshToken(Token $refreshToken): void
    {
        Cache::put($this->refreshTokenCacheKey, $refreshToken->toString());
    }

    public function retrieveAccessToken(): ?Token
    {
        return Cache::has($this->accessTokenCacheKey)
            ? (new Token\Parser(new JoseEncoder))->parse(Cache::get($this->accessTokenCacheKey))
            : null;
    }

    public function retrieveRefreshToken(): ?Token
    {
        return Cache::has($this->refreshTokenCacheKey)
            ? (new Token\Parser(new JoseEncoder))->parse(Cache::get($this->refreshTokenCacheKey))
            : null;
    }
}

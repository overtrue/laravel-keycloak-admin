<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Overtrue\LaravelKeycloakAdmin\LaravelCacheTokenStorage;

class LaravelCacheTokenStorageTest extends TestCase
{
    private LaravelCacheTokenStorage $tokenStorage;

    private Configuration $jwtConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = new LaravelCacheTokenStorage(
            accessTokenCacheKey: 'test-access-token',
            refreshTokenCacheKey: 'test-refresh-token'
        );

        $this->jwtConfig = Configuration::forSymmetricSigner(
            new Sha256,
            InMemory::plainText('your-256-bit-secret-your-256-bit-secret')
        );
    }

    public function test_can_store_and_retrieve_access_token(): void
    {
        $token = $this->jwtConfig->builder()
            ->identifiedBy('test-access-token')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $this->tokenStorage->storeAccessToken($token);

        $retrievedToken = $this->tokenStorage->retrieveAccessToken();

        $this->assertInstanceOf(Token::class, $retrievedToken);
        $this->assertEquals($token->toString(), $retrievedToken->toString());
    }

    public function test_can_store_and_retrieve_refresh_token(): void
    {
        $token = $this->jwtConfig->builder()
            ->identifiedBy('test-refresh-token')
            ->expiresAt(new \DateTimeImmutable('+1 day'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $this->tokenStorage->storeRefreshToken($token);

        $retrievedToken = $this->tokenStorage->retrieveRefreshToken();

        $this->assertInstanceOf(Token::class, $retrievedToken);
        $this->assertEquals($token->toString(), $retrievedToken->toString());
    }

    public function test_returns_null_when_access_token_not_found(): void
    {
        $retrievedToken = $this->tokenStorage->retrieveAccessToken();

        $this->assertNull($retrievedToken);
    }

    public function test_returns_null_when_refresh_token_not_found(): void
    {
        $retrievedToken = $this->tokenStorage->retrieveRefreshToken();

        $this->assertNull($retrievedToken);
    }

    public function test_uses_custom_cache_keys(): void
    {
        $customStorage = new LaravelCacheTokenStorage(
            accessTokenCacheKey: 'custom-access-key',
            refreshTokenCacheKey: 'custom-refresh-key'
        );

        $token = $this->jwtConfig->builder()
            ->identifiedBy('test-token')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $customStorage->storeAccessToken($token);

        $this->assertTrue(Cache::has('custom-access-key'));
        $this->assertFalse(Cache::has('test-access-token'));
    }

    public function test_overwrites_existing_tokens(): void
    {
        $token1 = $this->jwtConfig->builder()
            ->identifiedBy('token-1')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $token2 = $this->jwtConfig->builder()
            ->identifiedBy('token-2')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $this->tokenStorage->storeAccessToken($token1);
        $this->tokenStorage->storeAccessToken($token2);

        $retrievedToken = $this->tokenStorage->retrieveAccessToken();

        $this->assertEquals($token2->toString(), $retrievedToken->toString());
    }

    public function test_can_handle_default_cache_keys(): void
    {
        $defaultStorage = new LaravelCacheTokenStorage;

        $token = $this->jwtConfig->builder()
            ->identifiedBy('test-token')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $defaultStorage->storeAccessToken($token);

        $this->assertTrue(Cache::has('laravel-keycloak-admin-cache-token'));

        $retrievedToken = $defaultStorage->retrieveAccessToken();
        $this->assertEquals($token->toString(), $retrievedToken->toString());
    }
}

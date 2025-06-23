<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Overtrue\LaravelKeycloakAdmin\LaravelCacheTokenStorage;

class PerformanceTest extends TestCase
{
    private Configuration $jwtConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtConfig = Configuration::forSymmetricSigner(
            new Sha256,
            InMemory::plainText('your-256-bit-secret-your-256-bit-secret')
        );
    }

    public function test_multiple_token_operations_performance(): void
    {
        $storage = new LaravelCacheTokenStorage;

        $token = $this->jwtConfig->builder()
            ->identifiedBy('performance-test')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $start = microtime(true);

        // Perform multiple operations
        for ($i = 0; $i < 100; $i++) {
            $storage->storeAccessToken($token);
            $retrievedToken = $storage->retrieveAccessToken();
            $this->assertNotNull($retrievedToken);
        }

        $end = microtime(true);
        $duration = $end - $start;

        // Should complete 100 operations in less than 1 second
        $this->assertLessThan(1.0, $duration, 'Token operations should be fast');
    }

    public function test_memory_usage_with_large_tokens(): void
    {
        $storage = new LaravelCacheTokenStorage;

        // Create a token with large payload
        $token = $this->jwtConfig->builder()
            ->identifiedBy('large-token-test')
            ->withClaim('large_data', str_repeat('a', 1000))
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $memoryBefore = memory_get_usage();

        $storage->storeAccessToken($token);
        $retrievedToken = $storage->retrieveAccessToken();

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Should not use excessive memory (less than 1MB for this test)
        $this->assertLessThan(1024 * 1024, $memoryUsed, 'Memory usage should be reasonable');
        $this->assertNotNull($retrievedToken);
    }

    public function test_concurrent_cache_operations(): void
    {
        $storage1 = new LaravelCacheTokenStorage('token1', 'refresh1');
        $storage2 = new LaravelCacheTokenStorage('token2', 'refresh2');

        $token1 = $this->jwtConfig->builder()
            ->identifiedBy('concurrent-test-1')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $token2 = $this->jwtConfig->builder()
            ->identifiedBy('concurrent-test-2')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        // Store tokens concurrently
        $storage1->storeAccessToken($token1);
        $storage2->storeAccessToken($token2);

        // Retrieve tokens
        $retrieved1 = $storage1->retrieveAccessToken();
        $retrieved2 = $storage2->retrieveAccessToken();

        // Should not interfere with each other
        $this->assertEquals($token1->toString(), $retrieved1->toString());
        $this->assertEquals($token2->toString(), $retrieved2->toString());
    }

    public function test_service_singleton_performance(): void
    {
        $start = microtime(true);

        // Multiple calls should return the same instance quickly
        for ($i = 0; $i < 100; $i++) {
            $keycloak = $this->app->make(\Overtrue\Keycloak\Keycloak::class);
            $this->assertInstanceOf(\Overtrue\Keycloak\Keycloak::class, $keycloak);
        }

        $end = microtime(true);
        $duration = $end - $start;

        // Should be very fast due to singleton pattern
        $this->assertLessThan(0.1, $duration, 'Singleton resolution should be fast');
    }
}

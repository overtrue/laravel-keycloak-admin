<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Overtrue\LaravelKeycloakAdmin\LaravelCacheTokenStorage;

class EdgeCasesTest extends TestCase
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

    public function test_token_storage_with_very_long_cache_keys(): void
    {
        $longKey = str_repeat('a', 200);
        $storage = new LaravelCacheTokenStorage($longKey, $longKey.'-refresh');

        $token = $this->jwtConfig->builder()
            ->identifiedBy('long-key-test')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $storage->storeAccessToken($token);
        $retrievedToken = $storage->retrieveAccessToken();

        $this->assertEquals($token->toString(), $retrievedToken->toString());
    }

    public function test_token_storage_with_special_characters_in_keys(): void
    {
        $specialKey = 'key-with-special-chars-@#$%^&*()';
        $storage = new LaravelCacheTokenStorage($specialKey, $specialKey.'-refresh');

        $token = $this->jwtConfig->builder()
            ->identifiedBy('special-char-test')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $storage->storeAccessToken($token);
        $retrievedToken = $storage->retrieveAccessToken();

        $this->assertEquals($token->toString(), $retrievedToken->toString());
    }

    public function test_config_with_boolean_values(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);
        $this->assertTrue(config('keycloak-admin.use_laravel_cache'));

        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);
        $this->assertFalse(config('keycloak-admin.use_laravel_cache'));

        $this->app['config']->set('keycloak-admin.use_laravel_cache', 'true');
        $this->assertEquals('true', config('keycloak-admin.use_laravel_cache'));

        $this->app['config']->set('keycloak-admin.use_laravel_cache', '1');
        $this->assertEquals('1', config('keycloak-admin.use_laravel_cache'));
    }

    public function test_config_with_numeric_values(): void
    {
        $this->app['config']->set('keycloak-admin.base_url', 12345);
        $this->assertEquals(12345, config('keycloak-admin.base_url'));

        $this->app['config']->set('keycloak-admin.username', 0);
        $this->assertEquals(0, config('keycloak-admin.username'));
    }

    public function test_token_expiration_handling(): void
    {
        $storage = new LaravelCacheTokenStorage;

        // Create an expired token
        $expiredToken = $this->jwtConfig->builder()
            ->identifiedBy('expired-token')
            ->expiresAt(new \DateTimeImmutable('-1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $storage->storeAccessToken($expiredToken);
        $retrievedToken = $storage->retrieveAccessToken();

        // Should still retrieve the token (expiration handling is typically done by the consumer)
        $this->assertNotNull($retrievedToken);
        $this->assertEquals($expiredToken->toString(), $retrievedToken->toString());
    }

    public function test_multiple_storage_instances_with_same_keys(): void
    {
        $storage1 = new LaravelCacheTokenStorage('shared-key', 'shared-refresh-key');
        $storage2 = new LaravelCacheTokenStorage('shared-key', 'shared-refresh-key');

        $token = $this->jwtConfig->builder()
            ->identifiedBy('shared-key-test')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $storage1->storeAccessToken($token);
        $retrievedFromStorage2 = $storage2->retrieveAccessToken();

        // Both should access the same cached token
        $this->assertEquals($token->toString(), $retrievedFromStorage2->toString());
    }

    public function test_cache_driver_switching(): void
    {
        // Test with array cache driver
        $this->app['config']->set('cache.default', 'array');

        $storage = new LaravelCacheTokenStorage;

        $token = $this->jwtConfig->builder()
            ->identifiedBy('array-cache-test')
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        $storage->storeAccessToken($token);
        $retrievedToken = $storage->retrieveAccessToken();

        $this->assertEquals($token->toString(), $retrievedToken->toString());
    }

    public function test_config_merging_with_existing_values(): void
    {
        // Set some existing config
        $this->app['config']->set('keycloak-admin.custom_key', 'custom_value');

        // Re-register the provider to test merging
        $this->app->register(\Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider::class, true);

        // Custom config should still exist
        $this->assertEquals('custom_value', config('keycloak-admin.custom_key'));

        // Default config should also exist
        $this->assertEquals('http://localhost:8080', config('keycloak-admin.base_url'));
    }
}

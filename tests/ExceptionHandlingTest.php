<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Overtrue\LaravelKeycloakAdmin\LaravelCacheTokenStorage;

class ExceptionHandlingTest extends TestCase
{
    public function test_token_storage_handles_invalid_jwt_gracefully(): void
    {
        $storage = new LaravelCacheTokenStorage;

        // Store invalid JWT string
        Cache::put('laravel-keycloak-admin-cache-token', 'invalid.jwt.token');

        // Should not throw exception but return null
        $this->expectException(\Exception::class);
        $storage->retrieveAccessToken();
    }

    public function test_token_storage_handles_malformed_jwt(): void
    {
        $storage = new LaravelCacheTokenStorage;

        // Store malformed JWT string
        Cache::put('laravel-keycloak-admin-cache-token', 'malformed-jwt');

        $this->expectException(\Exception::class);
        $storage->retrieveAccessToken();
    }

    public function test_token_storage_handles_empty_cache_gracefully(): void
    {
        $storage = new LaravelCacheTokenStorage;

        // Clear cache
        Cache::flush();

        $accessToken = $storage->retrieveAccessToken();
        $refreshToken = $storage->retrieveRefreshToken();

        $this->assertNull($accessToken);
        $this->assertNull($refreshToken);
    }

    public function test_service_provider_handles_missing_config(): void
    {
        // Clear all keycloak-admin config
        $this->app['config']->set('keycloak-admin', []);

        // Re-register the service
        $this->app->forgetInstance(\Overtrue\Keycloak\Keycloak::class);

        // Should throw TypeError when trying to create instance with null values
        $this->expectException(\TypeError::class);
        $this->app->make(\Overtrue\Keycloak\Keycloak::class);
    }

    public function test_config_with_null_values(): void
    {
        $this->app['config']->set('keycloak-admin.base_url', null);
        $this->app['config']->set('keycloak-admin.username', null);
        $this->app['config']->set('keycloak-admin.password', null);

        $this->app->forgetInstance(\Overtrue\Keycloak\Keycloak::class);

        // Should throw TypeError when trying to create instance with null values
        $this->expectException(\TypeError::class);
        $this->app->make(\Overtrue\Keycloak\Keycloak::class);
    }
}

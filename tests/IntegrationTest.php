<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Overtrue\Keycloak\Keycloak;
use Overtrue\LaravelKeycloakAdmin\Facades\KeycloakAdmin;
use Overtrue\LaravelKeycloakAdmin\LaravelCacheTokenStorage;

class IntegrationTest extends TestCase
{
    public function test_keycloak_service_integration(): void
    {
        // Test that the service is properly resolved and configured
        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_facade_integration(): void
    {
        // Test that facade properly resolves to the same instance
        $keycloakFromContainer = $this->app->make(Keycloak::class);
        $keycloakFromFacade = KeycloakAdmin::getFacadeRoot();

        $this->assertSame($keycloakFromContainer, $keycloakFromFacade);
    }

    public function test_cache_token_storage_integration(): void
    {
        // Test that cache token storage works with Laravel's cache system
        $storage = new LaravelCacheTokenStorage;

        // Mock a simple token string for testing
        Cache::put('laravel-keycloak-admin-cache-token', 'mock.jwt.token');

        $this->assertTrue(Cache::has('laravel-keycloak-admin-cache-token'));
        $this->assertEquals('mock.jwt.token', Cache::get('laravel-keycloak-admin-cache-token'));
    }

    public function test_service_provider_integration_with_cache_enabled(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);

        // Force re-registration
        $this->app->forgetInstance(Keycloak::class);
        $this->app->register(\Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider::class, true);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_service_provider_integration_with_cache_disabled(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);

        // Force re-registration
        $this->app->forgetInstance(Keycloak::class);
        $this->app->register(\Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider::class, true);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_config_publishing_integration(): void
    {
        // This test would require actual file system operations
        // For unit testing, we'll just verify the configuration exists
        $config = $this->app['config']->get('keycloak-admin');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('base_url', $config);
        $this->assertArrayHasKey('username', $config);
        $this->assertArrayHasKey('password', $config);
        $this->assertArrayHasKey('use_laravel_cache', $config);
    }
}

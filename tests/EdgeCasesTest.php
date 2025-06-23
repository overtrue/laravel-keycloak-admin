<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Overtrue\Keycloak\Keycloak;

class EdgeCasesTest extends TestCase
{
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

    public function test_config_with_empty_strings(): void
    {
        $this->app['config']->set('keycloak-admin.base_url', '');
        $this->app['config']->set('keycloak-admin.username', '');
        $this->app['config']->set('keycloak-admin.password', '');

        $this->assertEquals('', config('keycloak-admin.base_url'));
        $this->assertEquals('', config('keycloak-admin.username'));
        $this->assertEquals('', config('keycloak-admin.password'));

        // Keycloak service should still be created (may throw exception during actual usage)
        $this->app->forgetInstance(Keycloak::class);

        // Empty strings might cause issues, but service should still be registered
        $keycloak = $this->app->make(Keycloak::class);
        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_cache_driver_switching(): void
    {
        // Test with array cache driver
        $this->app['config']->set('cache.default', 'array');

        Cache::put('test-cache-key', 'test-value', 60);

        $this->assertTrue(Cache::has('test-cache-key'));
        $this->assertEquals('test-value', Cache::get('test-cache-key'));

        Cache::forget('test-cache-key');
        $this->assertFalse(Cache::has('test-cache-key'));
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

    public function test_keycloak_service_with_different_cache_configurations(): void
    {
        // Test with cache enabled
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);
        $this->app['config']->set('keycloak-admin.base_url', 'http://localhost:8080');
        $this->app['config']->set('keycloak-admin.username', 'admin');
        $this->app['config']->set('keycloak-admin.password', 'admin');

        $this->app->forgetInstance(Keycloak::class);
        $keycloakWithCache = $this->app->make(Keycloak::class);

        // Test with cache disabled
        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);
        $this->app->forgetInstance(Keycloak::class);
        $keycloakWithoutCache = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloakWithCache);
        $this->assertInstanceOf(Keycloak::class, $keycloakWithoutCache);

        // They should be different instances due to different configurations
        $this->assertNotSame($keycloakWithCache, $keycloakWithoutCache);
    }

    public function test_service_provider_with_missing_optional_config(): void
    {
        // Remove optional config and set required ones
        $config = [
            'base_url' => 'http://localhost:8080',
            'username' => 'admin',
            'password' => 'admin',
            'use_laravel_cache' => true,
        ];

        $this->app['config']->set('keycloak-admin', $config);
        $this->app->forgetInstance(Keycloak::class);

        $keycloak = $this->app->make(Keycloak::class);
        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_multiple_service_instances_are_same(): void
    {
        // Verify singleton behavior in edge cases
        $keycloak1 = $this->app->make(Keycloak::class);
        $keycloak2 = $this->app->make('keycloak-admin');
        $keycloak3 = $this->app->make(Keycloak::class);

        $this->assertSame($keycloak1, $keycloak2);
        $this->assertSame($keycloak1, $keycloak3);
        $this->assertSame($keycloak2, $keycloak3);
    }
}

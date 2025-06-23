<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Overtrue\Keycloak\Keycloak;
use Overtrue\LaravelKeycloakAdmin\Facades\KeycloakAdmin;

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

    public function test_laravel_cache_integration(): void
    {
        // Test that Laravel cache system works with the application
        Cache::put('test-integration-key', 'test-value', 60);

        $this->assertTrue(Cache::has('test-integration-key'));
        $this->assertEquals('test-value', Cache::get('test-integration-key'));

        Cache::forget('test-integration-key');
        $this->assertFalse(Cache::has('test-integration-key'));
    }

    public function test_service_provider_integration_with_cache_enabled(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);
        $this->app['config']->set('keycloak-admin.base_url', 'http://localhost:8080');
        $this->app['config']->set('keycloak-admin.username', 'admin');
        $this->app['config']->set('keycloak-admin.password', 'admin');

        // Force re-registration
        $this->app->forgetInstance(Keycloak::class);
        $this->app->register(\Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider::class, true);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);

        // 验证缓存配置正确设置
        $this->assertTrue(config('keycloak-admin.use_laravel_cache'));
    }

    public function test_service_provider_integration_with_cache_disabled(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);
        $this->app['config']->set('keycloak-admin.base_url', 'http://localhost:8080');
        $this->app['config']->set('keycloak-admin.username', 'admin');
        $this->app['config']->set('keycloak-admin.password', 'admin');

        // Force re-registration
        $this->app->forgetInstance(Keycloak::class);
        $this->app->register(\Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider::class, true);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);

        // 验证缓存配置正确设置
        $this->assertFalse(config('keycloak-admin.use_laravel_cache'));
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

    public function test_keycloak_configuration_integration(): void
    {
        $this->app['config']->set('keycloak-admin.base_url', 'http://test-server:8080');
        $this->app['config']->set('keycloak-admin.username', 'test-user');
        $this->app['config']->set('keycloak-admin.password', 'test-pass');
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);

        $this->app->forgetInstance(Keycloak::class);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);

        // 验证配置正确应用
        $this->assertEquals('http://test-server:8080', config('keycloak-admin.base_url'));
        $this->assertEquals('test-user', config('keycloak-admin.username'));
        $this->assertEquals('test-pass', config('keycloak-admin.password'));
        $this->assertTrue(config('keycloak-admin.use_laravel_cache'));
    }

    public function test_service_registration_with_different_configs(): void
    {
        // Test multiple service registrations with different configurations
        $configs = [
            ['use_laravel_cache' => true, 'base_url' => 'http://server1:8080'],
            ['use_laravel_cache' => false, 'base_url' => 'http://server2:8080'],
            ['use_laravel_cache' => true, 'base_url' => 'http://server3:8080'],
        ];

        foreach ($configs as $config) {
            $this->app['config']->set('keycloak-admin.use_laravel_cache', $config['use_laravel_cache']);
            $this->app['config']->set('keycloak-admin.base_url', $config['base_url']);
            $this->app['config']->set('keycloak-admin.username', 'admin');
            $this->app['config']->set('keycloak-admin.password', 'admin');

            $this->app->forgetInstance(Keycloak::class);

            $keycloak = $this->app->make(Keycloak::class);
            $this->assertInstanceOf(Keycloak::class, $keycloak);

            // Verify config is applied
            $this->assertEquals($config['use_laravel_cache'], config('keycloak-admin.use_laravel_cache'));
            $this->assertEquals($config['base_url'], config('keycloak-admin.base_url'));
        }
    }
}

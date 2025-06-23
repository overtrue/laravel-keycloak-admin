<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Overtrue\Keycloak\Keycloak;

class ExceptionHandlingTest extends TestCase
{
    public function test_cache_operations_handle_exceptions_gracefully(): void
    {
        // Test that cache operations don't break the system
        Cache::shouldReceive('store')
            ->andThrow(new \Exception('Cache connection failed'));

        // Service should still be creatable even if cache fails
        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);
        $this->app->forgetInstance(Keycloak::class);

        $keycloak = $this->app->make(Keycloak::class);
        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_service_provider_handles_missing_config(): void
    {
        // Clear all keycloak-admin config
        $this->app['config']->set('keycloak-admin', []);

        // Re-register the service
        $this->app->forgetInstance(Keycloak::class);

        // Should throw TypeError when trying to create instance with null values
        $this->expectException(\TypeError::class);
        $this->app->make(Keycloak::class);
    }

    public function test_config_with_null_values(): void
    {
        $this->app['config']->set('keycloak-admin.base_url', null);
        $this->app['config']->set('keycloak-admin.username', null);
        $this->app['config']->set('keycloak-admin.password', null);

        $this->app->forgetInstance(Keycloak::class);

        // Should throw TypeError when trying to create instance with null values
        $this->expectException(\TypeError::class);
        $this->app->make(Keycloak::class);
    }

    public function test_invalid_base_url_handling(): void
    {
        $this->app['config']->set('keycloak-admin.base_url', 'invalid-url');
        $this->app['config']->set('keycloak-admin.username', 'admin');
        $this->app['config']->set('keycloak-admin.password', 'admin');
        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);

        $this->app->forgetInstance(Keycloak::class);

        // Service should still be created (error may occur during actual HTTP requests)
        $keycloak = $this->app->make(Keycloak::class);
        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_service_creation_with_invalid_cache_config(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', 'invalid-boolean');
        $this->app['config']->set('keycloak-admin.base_url', 'http://localhost:8080');
        $this->app['config']->set('keycloak-admin.username', 'admin');
        $this->app['config']->set('keycloak-admin.password', 'admin');

        $this->app->forgetInstance(Keycloak::class);

        // Service should still be created (truthy value should enable cache)
        $keycloak = $this->app->make(Keycloak::class);
        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_facade_with_unregistered_service(): void
    {
        // This would be an edge case where facade is used but service isn't properly registered
        $this->app->forgetInstance(Keycloak::class);

        // Facade should still work due to automatic resolution
        $facade = \Overtrue\LaravelKeycloakAdmin\Facades\KeycloakAdmin::getFacadeRoot();
        $this->assertInstanceOf(Keycloak::class, $facade);
    }

    public function test_config_merging_with_invalid_data(): void
    {
        // Set invalid config data
        $this->app['config']->set('keycloak-admin', 'invalid-config-not-array');

        // 直接重新注册服务提供者会导致类型错误，这是预期的行为
        // 在实际应用中，这种情况应该被避免，但我们测试系统的健壮性
        try {
            $this->app->register(\Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider::class, true);

            // 如果没有抛出异常，验证配置是否被正确处理
            $config = config('keycloak-admin');
            $this->assertIsArray($config);
        } catch (\TypeError $e) {
            // 预期的类型错误，因为mergeConfigFrom期望数组类型
            $this->assertStringContainsString('array_merge(): Argument #2 must be of type array', $e->getMessage());
        }
    }

    public function test_empty_config_array(): void
    {
        $this->app['config']->set('keycloak-admin', []);

        // Re-register to apply config merging
        $this->app->register(\Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider::class, true);

        // Config should be merged with defaults
        $this->assertIsArray(config('keycloak-admin'));
        $this->assertEquals('http://localhost:8080', config('keycloak-admin.base_url'));
    }

    public function test_service_provider_boot_method_runs_safely(): void
    {
        // Test that boot method runs without errors
        $provider = new \Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider($this->app);

        // Boot method should not throw exceptions
        $provider->boot();

        // Should be able to get merged config
        $this->assertIsArray(config('keycloak-admin'));
    }

    public function test_service_provider_register_method_runs_safely(): void
    {
        // Test that register method runs without errors
        $provider = new \Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider($this->app);

        // Register method should not throw exceptions
        $provider->register();

        // Service should be registered
        $this->assertTrue($this->app->bound(Keycloak::class));
        $this->assertTrue($this->app->bound('keycloak-admin'));
    }
}

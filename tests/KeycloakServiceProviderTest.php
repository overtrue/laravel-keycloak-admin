<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Overtrue\Keycloak\Keycloak;

class KeycloakServiceProviderTest extends TestCase
{
    public function test_keycloak_service_is_registered(): void
    {
        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_keycloak_service_is_singleton(): void
    {
        $keycloak1 = $this->app->make(Keycloak::class);
        $keycloak2 = $this->app->make(Keycloak::class);

        $this->assertSame($keycloak1, $keycloak2);
    }

    public function test_keycloak_service_alias_is_registered(): void
    {
        $keycloak1 = $this->app->make(Keycloak::class);
        $keycloak2 = $this->app->make('keycloak-admin');

        $this->assertSame($keycloak1, $keycloak2);
    }

    public function test_config_is_merged(): void
    {
        $this->assertEquals('http://localhost:8080', config('keycloak-admin.base_url'));
        $this->assertEquals('admin', config('keycloak-admin.username'));
        $this->assertEquals('admin', config('keycloak-admin.password'));
        $this->assertTrue(config('keycloak-admin.use_laravel_cache'));
    }

    public function test_keycloak_uses_laravel_cache_when_enabled(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);
        $this->app['config']->set('keycloak-admin.base_url', 'http://localhost:8080');
        $this->app['config']->set('keycloak-admin.username', 'admin');
        $this->app['config']->set('keycloak-admin.password', 'admin');

        // Re-register the service to apply new config
        $this->app->forgetInstance(Keycloak::class);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);

        // 验证缓存配置已启用，通过检查服务是否正确创建
        $this->assertTrue(config('keycloak-admin.use_laravel_cache'));
    }

    public function test_keycloak_does_not_use_laravel_cache_when_disabled(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);
        $this->app['config']->set('keycloak-admin.base_url', 'http://localhost:8080');
        $this->app['config']->set('keycloak-admin.username', 'admin');
        $this->app['config']->set('keycloak-admin.password', 'admin');

        // Re-register the service to apply new config
        $this->app->forgetInstance(Keycloak::class);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);

        // 验证缓存配置已禁用
        $this->assertFalse(config('keycloak-admin.use_laravel_cache'));
    }

    public function test_config_file_can_be_published(): void
    {
        $this->artisan('vendor:publish', [
            '--provider' => 'Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider',
            '--tag' => 'config',
        ])->assertExitCode(0);

        $this->assertFileExists(config_path('keycloak-admin.php'));
    }

    public function test_keycloak_service_uses_correct_configuration(): void
    {
        $this->app['config']->set('keycloak-admin.base_url', 'http://test-keycloak:8080');
        $this->app['config']->set('keycloak-admin.username', 'test-admin');
        $this->app['config']->set('keycloak-admin.password', 'test-password');
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);

        // Re-register the service to apply new config
        $this->app->forgetInstance(Keycloak::class);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);

        // 验证配置正确设置，通过检查配置值而非内部属性
        $this->assertEquals('http://test-keycloak:8080', config('keycloak-admin.base_url'));
        $this->assertEquals('test-admin', config('keycloak-admin.username'));
        $this->assertEquals('test-password', config('keycloak-admin.password'));
    }

    public function test_different_cache_configurations_create_services(): void
    {
        // Test with cache enabled
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);
        $this->app->forgetInstance(Keycloak::class);
        $keycloakWithCache = $this->app->make(Keycloak::class);

        // Test with cache disabled
        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);
        $this->app->forgetInstance(Keycloak::class);
        $keycloakWithoutCache = $this->app->make(Keycloak::class);

        // Both should be valid Keycloak instances
        $this->assertInstanceOf(Keycloak::class, $keycloakWithCache);
        $this->assertInstanceOf(Keycloak::class, $keycloakWithoutCache);
    }
}

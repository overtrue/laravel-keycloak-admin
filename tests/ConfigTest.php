<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

class ConfigTest extends TestCase
{
    public function test_default_config_values(): void
    {
        $config = include __DIR__.'/../config/keycloak-admin.php';

        $this->assertIsArray($config);
        $this->assertArrayHasKey('base_url', $config);
        $this->assertArrayHasKey('username', $config);
        $this->assertArrayHasKey('password', $config);
        $this->assertArrayHasKey('use_laravel_cache', $config);
    }

    public function test_config_uses_environment_variables(): void
    {
        $_ENV['KEYCLOAK_ADMIN_BASE_URL'] = 'http://custom-keycloak:8080';
        $_ENV['KEYCLOAK_ADMIN_USERNAME'] = 'custom-admin';
        $_ENV['KEYCLOAK_ADMIN_PASSWORD'] = 'custom-password';
        $_ENV['KEYCLOAK_ADMIN_USE_LARAVEL_CACHE'] = false;

        $this->app['config']->set('keycloak-admin.base_url', env('KEYCLOAK_ADMIN_BASE_URL', 'http://localhost:8080'));
        $this->app['config']->set('keycloak-admin.username', env('KEYCLOAK_ADMIN_USERNAME', 'admin'));
        $this->app['config']->set('keycloak-admin.password', env('KEYCLOAK_ADMIN_PASSWORD', 'admin'));
        $this->app['config']->set('keycloak-admin.use_laravel_cache', env('KEYCLOAK_ADMIN_USE_LARAVEL_CACHE', true));

        $this->assertEquals('http://custom-keycloak:8080', config('keycloak-admin.base_url'));
        $this->assertEquals('custom-admin', config('keycloak-admin.username'));
        $this->assertEquals('custom-password', config('keycloak-admin.password'));
        $this->assertFalse(config('keycloak-admin.use_laravel_cache'));

        // Clean up
        unset($_ENV['KEYCLOAK_ADMIN_BASE_URL']);
        unset($_ENV['KEYCLOAK_ADMIN_USERNAME']);
        unset($_ENV['KEYCLOAK_ADMIN_PASSWORD']);
        unset($_ENV['KEYCLOAK_ADMIN_USE_LARAVEL_CACHE']);
    }

    public function test_config_fallback_to_keycloak_base_url(): void
    {
        $_ENV['KEYCLOAK_BASE_URL'] = 'http://fallback-keycloak:8080';

        $this->app['config']->set('keycloak-admin.base_url', env('KEYCLOAK_ADMIN_BASE_URL', env('KEYCLOAK_BASE_URL', 'http://localhost:8080')));

        $this->assertEquals('http://fallback-keycloak:8080', config('keycloak-admin.base_url'));

        // Clean up
        unset($_ENV['KEYCLOAK_BASE_URL']);
    }

    public function test_use_laravel_cache_config(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);
        $this->assertTrue(config('keycloak-admin.use_laravel_cache'));

        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);
        $this->assertFalse(config('keycloak-admin.use_laravel_cache'));
    }
}

<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

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

        // Re-register the service to apply new config
        $this->app->forgetInstance(Keycloak::class);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_keycloak_does_not_use_laravel_cache_when_disabled(): void
    {
        $this->app['config']->set('keycloak-admin.use_laravel_cache', false);

        // Re-register the service to apply new config
        $this->app->forgetInstance(Keycloak::class);

        $keycloak = $this->app->make(Keycloak::class);

        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_config_file_can_be_published(): void
    {
        $this->artisan('vendor:publish', [
            '--provider' => 'Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider',
            '--tag' => 'config',
        ])->assertExitCode(0);

        $this->assertFileExists(config_path('keycloak-admin.php'));
    }
}

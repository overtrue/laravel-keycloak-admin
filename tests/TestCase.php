<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('keycloak-admin.base_url', 'http://localhost:8080');
        $this->app['config']->set('keycloak-admin.username', 'admin');
        $this->app['config']->set('keycloak-admin.password', 'admin');
        $this->app['config']->set('keycloak-admin.use_laravel_cache', true);
    }

    protected function getPackageProviders($app): array
    {
        return [
            KeycloakServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'KeycloakAdmin' => \Overtrue\LaravelKeycloakAdmin\Facades\KeycloakAdmin::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
    }
}

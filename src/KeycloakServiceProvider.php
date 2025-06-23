<?php

namespace Overtrue\LaravelKeycloakAdmin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Overtrue\Keycloak\Keycloak;

class KeycloakServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/keycloak-admin.php' => config_path('keycloak-admin.php'),
        ], 'config');
        $this->mergeConfigFrom(__DIR__.'/../config/keycloak-admin.php', 'keycloak-admin');
    }

    public function register(): void
    {
        $this->app->singleton(Keycloak::class, function () {
            if (config('keycloak-admin.use_laravel_cache', true)) {
                return new Keycloak(
                    baseUrl: config('keycloak-admin.base_url'),
                    username: config('keycloak-admin.username'),
                    password: config('keycloak-admin.password'),
                    cache: Cache::store()
                );
            }

            return new Keycloak(
                baseUrl: config('keycloak-admin.base_url'),
                username: config('keycloak-admin.username'),
                password: config('keycloak-admin.password'),
            );
        });

        $this->app->alias(Keycloak::class, 'keycloak-admin');
    }
}

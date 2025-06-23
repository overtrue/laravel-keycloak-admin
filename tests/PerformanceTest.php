<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Illuminate\Support\Facades\Cache;
use Overtrue\Keycloak\Keycloak;

class PerformanceTest extends TestCase
{
    public function test_cache_operations_performance(): void
    {
        $start = microtime(true);

        // Perform multiple cache operations
        for ($i = 0; $i < 100; $i++) {
            Cache::put("test-key-{$i}", "test-value-{$i}", 60);
            $value = Cache::get("test-key-{$i}");
            $this->assertEquals("test-value-{$i}", $value);
            Cache::forget("test-key-{$i}");
        }

        $end = microtime(true);
        $duration = $end - $start;

        // Should complete 100 operations in less than 1 second
        $this->assertLessThan(1.0, $duration, 'Cache operations should be fast');
    }

    public function test_service_singleton_performance(): void
    {
        $start = microtime(true);

        // Multiple calls should return the same instance quickly
        for ($i = 0; $i < 100; $i++) {
            $keycloak = $this->app->make(Keycloak::class);
            $this->assertInstanceOf(Keycloak::class, $keycloak);
        }

        $end = microtime(true);
        $duration = $end - $start;

        // Should be very fast due to singleton pattern
        $this->assertLessThan(0.1, $duration, 'Singleton resolution should be fast');
    }

    public function test_config_access_performance(): void
    {
        $start = microtime(true);

        // Multiple config accesses should be fast
        for ($i = 0; $i < 1000; $i++) {
            $baseUrl = config('keycloak-admin.base_url');
            $username = config('keycloak-admin.username');
            $password = config('keycloak-admin.password');
            $useCache = config('keycloak-admin.use_laravel_cache');

            $this->assertNotNull($baseUrl);
        }

        $end = microtime(true);
        $duration = $end - $start;

        // Should be very fast
        $this->assertLessThan(0.1, $duration, 'Config access should be fast');
    }

    public function test_facade_performance(): void
    {
        $start = microtime(true);

        // Multiple facade calls should be fast
        for ($i = 0; $i < 100; $i++) {
            $facade = \Overtrue\LaravelKeycloakAdmin\Facades\KeycloakAdmin::getFacadeRoot();
            $this->assertInstanceOf(Keycloak::class, $facade);
        }

        $end = microtime(true);
        $duration = $end - $start;

        // Should be fast
        $this->assertLessThan(0.1, $duration, 'Facade resolution should be fast');
    }

    public function test_service_provider_registration_performance(): void
    {
        $start = microtime(true);

        // Register service provider multiple times (should be no-op after first time)
        for ($i = 0; $i < 10; $i++) {
            $this->app->register(\Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider::class);
        }

        $end = microtime(true);
        $duration = $end - $start;

        // Should be fast even with multiple registrations
        $this->assertLessThan(0.1, $duration, 'Service provider registration should be fast');
    }

    public function test_memory_usage_with_large_config(): void
    {
        $memoryBefore = memory_get_usage();

        // Set large config values
        $this->app['config']->set('keycloak-admin.base_url', str_repeat('http://localhost:8080/', 100));
        $this->app['config']->set('keycloak-admin.username', str_repeat('admin', 100));
        $this->app['config']->set('keycloak-admin.password', str_repeat('password', 100));

        $this->app->forgetInstance(Keycloak::class);
        $keycloak = $this->app->make(Keycloak::class);

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Should not use excessive memory (less than 1MB for this test)
        $this->assertLessThan(1024 * 1024, $memoryUsed, 'Memory usage should be reasonable');
        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_concurrent_service_access(): void
    {
        // Simulate concurrent access by rapidly creating multiple instances
        $services = [];

        $start = microtime(true);

        for ($i = 0; $i < 50; $i++) {
            $services[] = $this->app->make(Keycloak::class);
        }

        $end = microtime(true);
        $duration = $end - $start;

        // All should be the same instance (singleton)
        $firstService = $services[0];
        foreach ($services as $service) {
            $this->assertSame($firstService, $service);
        }

        // Should be fast
        $this->assertLessThan(0.1, $duration, 'Concurrent service access should be fast');
    }

    public function test_cache_store_switching_performance(): void
    {
        $start = microtime(true);

        // Switch cache configurations multiple times
        for ($i = 0; $i < 20; $i++) {
            $this->app['config']->set('keycloak-admin.use_laravel_cache', $i % 2 === 0);
            $this->app->forgetInstance(Keycloak::class);
            $keycloak = $this->app->make(Keycloak::class);
            $this->assertInstanceOf(Keycloak::class, $keycloak);
        }

        $end = microtime(true);
        $duration = $end - $start;

        // Should handle configuration changes reasonably fast
        $this->assertLessThan(1.0, $duration, 'Configuration switching should be reasonably fast');
    }
}

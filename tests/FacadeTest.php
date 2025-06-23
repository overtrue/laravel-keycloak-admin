<?php

namespace Overtrue\LaravelKeycloakAdmin\Tests;

use Overtrue\Keycloak\Keycloak;
use Overtrue\LaravelKeycloakAdmin\Facades\KeycloakAdmin;

class FacadeTest extends TestCase
{
    public function test_facade_resolves_to_keycloak_instance(): void
    {
        $keycloak = KeycloakAdmin::getFacadeRoot();

        $this->assertInstanceOf(Keycloak::class, $keycloak);
    }

    public function test_facade_accessor_returns_correct_service(): void
    {
        $accessor = KeycloakAdmin::getFacadeAccessor();

        $this->assertEquals(Keycloak::class, $accessor);
    }

    public function test_facade_is_registered_as_alias(): void
    {
        $facade = KeycloakAdmin::getFacadeRoot();
        $keycloak = $this->app->make(Keycloak::class);

        $this->assertSame($keycloak, $facade);
    }
}

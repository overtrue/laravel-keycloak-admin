<?php

namespace Overtrue\LaravelKeycloakAdmin\Facades;

use Illuminate\Support\Facades\Facade;
use Overtrue\Keycloak\Keycloak;
use Overtrue\Keycloak\Resource\AttackDetection;
use Overtrue\Keycloak\Resource\Clients;
use Overtrue\Keycloak\Resource\Groups;
use Overtrue\Keycloak\Resource\Organizations;
use Overtrue\Keycloak\Resource\Realms;
use Overtrue\Keycloak\Resource\Roles;
use Overtrue\Keycloak\Resource\ServerInfo;
use Overtrue\Keycloak\Resource\Users;

/**
 * @method static Users users()
 * @method static Roles roles()
 * @method static Realms realms()
 * @method static Groups groups()
 * @method static Clients clients()
 * @method static Organizations organizations()
 * @method static AttackDetection attackDetection()
 * @method static string getVersion()
 * @method static ServerInfo serverInfo()
 */
class KeycloakAdmin extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return Keycloak::class;
    }
}

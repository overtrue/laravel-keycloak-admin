<?php

namespace Overtrue\LaravelKeycloakAdmin\Facades;

use Fschmtt\Keycloak\Keycloak;
use Fschmtt\Keycloak\Resource\AttackDetection;
use Fschmtt\Keycloak\Resource\Clients;
use Fschmtt\Keycloak\Resource\Groups;
use Fschmtt\Keycloak\Resource\Organizations;
use Fschmtt\Keycloak\Resource\Realms;
use Fschmtt\Keycloak\Resource\Roles;
use Fschmtt\Keycloak\Resource\ServerInfo;
use Fschmtt\Keycloak\Resource\Users;
use Illuminate\Support\Facades\Facade;
use Overtrue\KeycloakAdmin\Client;

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

<?php

namespace Overtrue\LaravelKeycloakAdmin\Facades;

use Fschmtt\Keycloak\Keycloak;
use Illuminate\Support\Facades\Facade;
use Overtrue\KeycloakAdmin\Client;
use Overtrue\KeycloakAdmin\Resources\ClientResourceInterface;
use Overtrue\KeycloakAdmin\Resources\ClientsResourceInterface;
use Overtrue\KeycloakAdmin\Resources\RealmResourceInterface;
use Overtrue\KeycloakAdmin\Resources\RealmsResourceInterface;
use Overtrue\KeycloakAdmin\Resources\RolesResourceInterface;
use Overtrue\KeycloakAdmin\Resources\UserResourceInterface;
use Overtrue\KeycloakAdmin\Resources\UsersResourceInterface;

/**
 * @method static UsersResourceInterface users()
 * @method static UserResourceInterface user(string $id)
 * @method static RolesResourceInterface roles()
 * @method static ClientsResourceInterface clients()
 * @method static Client connection(?string $client)
 * @method static RealmResourceInterface realm(?string $realm = null)
 * @method static ClientResourceInterface client(?string $id = null)
 * @method static RealmsResourceInterface realms()
 */
class KeycloakAdmin extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return Keycloak::class;
    }
}

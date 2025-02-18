# Keycloak Admin API Client for Laravel

Package which adds some wrappers for Laravel on top of the [fschmtt/keycloak-rest-api-client-php](fschmtt/keycloak-rest-api-client-php)
library.

# Install

Install the package with composer
```
composer require overtrue/laravel-keycloak-admin
```

Publish the config file

```
php artisan vendor:publish  --provider="Overtrue\LaravelKeycloakAdmin\KeycloakServiceProvider"

```

# Usage

```php
use Overtrue\LaravelKeycloakAdmin\Facades\KeycloakAdmin;

// Get all users
$users = KeycloakAdmin::users()->all();

// Get a user by id
$user = KeycloakAdmin::users()->get('realm', 'user-id');

// Get all groups
KeycloakAdmin::groups()->all();

// Get a group by id
KeycloakAdmin::groups()->get('realm', 'group-id');

// Get all clients
KeycloakAdmin::clients()->all();

// Get a client by id
KeycloakAdmin::clients()->get('realm', 'client-id');

// Get all roles
KeycloakAdmin::roles()->all();

// Get a role by name
KeycloakAdmin::roles()->get('realm', 'role-name');

// Get all realms
KeycloakAdmin::realms()->all();

// Get a realm by name
KeycloakAdmin::realms()->get('realm-name');
```

More methods can be found in the [fschmtt/keycloak-rest-api-client-php](fschmtt/keycloak-rest-api-client-php) library.

# LICENSE

MIT



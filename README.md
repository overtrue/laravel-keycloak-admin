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
```

# LICENSE

MIT



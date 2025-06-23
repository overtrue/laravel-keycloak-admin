<?php

return [
    'base_url' => env('KEYCLOAK_ADMIN_BASE_URL', env('KEYCLOAK_BASE_URL', 'http://localhost:8080')),
    'username' => env('KEYCLOAK_ADMIN_USERNAME', 'admin'),
    'password' => env('KEYCLOAK_ADMIN_PASSWORD', 'admin'),
    'use_laravel_cache' => env('KEYCLOAK_ADMIN_USE_LARAVEL_CACHE', true),

    // cache
    //    'access_token_cache_key' => env('KEYCLOAK_ADMIN_ACCESS_TOKEN_CACHE_KEY', 'laravel-keycloak-admin-cache-token'),
    //    'refresh_token_cache_key' => env('KEYCLOAK_ADMIN_REFRESH_TOKEN_CACHE_KEY', 'laravel-keycloak-admin-cache-refresh-token'),
];

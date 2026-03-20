# AGENTS.md

Package: **overtrue/laravel-keycloak-admin**

## Purpose
Laravel package for interacting with Keycloak Admin APIs.

## Supported Platforms
- PHP: **^8.4** (Laravel 13 requires PHP 8.3+)
- Laravel: tested via **orchestra/testbench ^11** (Laravel 13)

## Local Development
```bash
composer install
composer test
```

## Quality
- Keep tests green (`composer test`).
- Keep formatting consistent (Pint) when applicable.

## Release Process (maintainer)
- Ensure CI is green.
- Tag & publish via GitHub releases / `gh`.

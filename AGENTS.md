# AGENTS.md

## Maintenance guidelines

- Prefer minimal, high-confidence changes.
- Do not change public APIs unless the issue explicitly requires it.
- Do not introduce new dependencies without a clear reason.
- Keep diffs small and reviewable.
- For PHP projects, check composer scripts first; prefer the smallest relevant PHPUnit/Pest/PHPStan command.
- For JavaScript/TypeScript projects, check package scripts first; prefer targeted tests/lint/typecheck.
- Never merge PRs, publish releases, close controversial issues, or modify security policy automatically.

## Review guidelines

- Flag regressions, missing tests, BC breaks, security risks, and unclear behavior.
- Do not block on subjective style unless it violates existing project conventions.
- Treat documentation typos as low priority unless they change meaning.
- When suggesting changes, be specific and include the reason.

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

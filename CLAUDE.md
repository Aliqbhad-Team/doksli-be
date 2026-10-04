# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project state

`doksli-be` is the backend (JSON API) for the Doksli document-management app, on Laravel 13 (PHP ^8.3, PHPUnit 12). Built so far: CRUD endpoints for `roles`, `units` and `users` under `/api/v1` (`apiResource`, routes in `routes/api/v1.php`). There is **no authentication yet** (no Sanctum, no login): `POST` and `GET` endpoints are open, and `PUT/PATCH/DELETE` return 403 for everyone outside tests (which use `actingAs`). The `*Policy` classes are placeholders that allow any authenticated user.

## Endpoint pattern (follow this for new modules)

Architecture is Laravel's standard flow: Route (`routes/api/v1.php`, prefixed `/api/v1`) → FormRequest → Controller → Model → API Resource. Reference implementation: the Users module (`UserController`, `Requests/User/*`, `UserResource`, `UserPolicy`, `tests/Feature/Api/V1/UserTest.php`). Deleting a role that still has users returns 409 (FK is restrict); deleting a unit nulls `users.unit_id`.

- **Controller** (`app/Http/Controllers/Api/V1/`): calls Eloquent directly for simple CRUD and returns a Resource. Keep it thin; don't put multi-step business logic here.
- **FormRequest** (`app/Http/Requests/{Domain}/`): all input validation. Update/delete authorize via `$this->user()?->can(...)`, so an unauthenticated caller gets 403.
- **Extract a class only when needed**: when an operation touches more than one table (wrap in `DB::transaction()`), is reused elsewhere (job/command), or has real business rules, move it to an Action (`app/Actions/{Domain}/{UseCase}.php`, plain data in, Model out, never `Request`/JSON). Don't add Service/Repository layers for plain CRUD.
- **Model**: relations, scopes and casts only. Statuses are PHP Enums in `app/Enums/`.
- **Policy** (`app/Policies`): ownership/authorization rules. **API Resource** (`Http/Resources/Api/V1`): JSON shape; relations via `whenLoaded()`.
- API conventions: status codes 200/201/204/403/404/422; lists are paginated and relations eager loaded (avoid N+1).
## Commands

```sh
composer setup          # install, create .env, key:generate, migrate, npm install, npm run build
composer dev            # runs `php artisan dev` (dev server + supporting processes)
composer test           # config:clear, then `php artisan test`
php artisan test --filter=ExampleTest          # single test class/method by name
php artisan test tests/Feature/ExampleTest.php # single file
vendor/bin/pint         # code style (Laravel Pint); add --dirty to only touch changed files
php artisan pail        # tail logs
npm run dev | npm run build   # Vite (frontend assets only)
```

Tests run against in-memory SQLite with array cache/session and sync queue (set in `phpunit.xml`), so they don't need a configured database.

## Architecture notes

- **Bootstrap is in `bootstrap/app.php`** (Laravel 11+ style): routing, middleware, and exception handling are configured there; there is no `Http/Kernel.php` or `Exceptions/Handler.php`. `web`, `api` and `console` routes are registered, plus `/up` as the health check. `routes/api.php` only mounts `routes/api/v1.php` under `/api/v1`; add new v1 routes there.
- **New tables use UUID primary keys** (`HasUuids` on the model, `$table->uuid('id')->primary()` in the migration).
- **Users schema differs from Laravel's default**: `password_hash` instead of `password` (`getAuthPasswordName()` is overridden, cast is `hashed`; the API field is still `password`), `status` is the `UserStatus` enum (default `active`), only `created_at` exists (`UPDATED_AT = null`), no `remember_token`, `role_id` is a required FK (restrict on delete) and `unit_id` a nullable FK (null on delete). The users migration is numbered `0001_01_01_000003` so it runs after `roles` and `units` (both `0001_01_01_000000`).
- **`.env` uses a remote MariaDB** (`DB_CONNECTION=mariadb`, server 10.11) while `.env.example` and tests use SQLite. Don't run `php artisan migrate` / `migrate:fresh` / `db:seed` against it without the user's go-ahead; `composer test` is safe (in-memory SQLite).
- **JSON errors are already forced** for `api/*` paths and any request that expects JSON (`shouldRenderJsonWhen` in `bootstrap/app.php`).
- **Models use PHP attributes** instead of properties: `#[Fillable([...])]` and `#[Hidden([...])]` on `App\Models\User`. Follow that style for new models rather than `$fillable`/`$hidden`. Casts are defined via a `casts()` method.
- **Database-backed infrastructure by default**: `.env.example` sets `SESSION_DRIVER`, `CACHE_STORE`, and `QUEUE_CONNECTION` to `database`, with SQLite as the DB (`database/database.sqlite`). A queue worker is needed for queued jobs in local dev unless `QUEUE_CONNECTION` is changed.
- **Frontend** is only Vite + Tailwind CSS 4 (`resources/css/app.css`, `resources/js/app.js`, `resources/views/welcome.blade.php`); it is not a SPA setup.
- `AppServiceProvider` is empty; it is the only registered provider (`bootstrap/providers.php`).

## Repo conventions

- Line endings are LF: `.gitattributes` has `* text=auto eol=lf` and `.editorconfig` has `end_of_line = lf`. Don't introduce CRLF.
- Laravel Boost is **not** installed (not in `composer.json`), even though `AGENTS.md` says to install it. Don't run `composer require laravel/boost` unless the user asks.
- Git identity for this repo is `faulna <fadhilmaulana2014@gmail.com>`; the remote is `Aliqbhad-Team/doksli-be` on GitHub, branch `main`.

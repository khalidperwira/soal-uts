# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repo layout

This repo has two independent parts:

- `app/` — the Laravel 13 backend (PHP 8.3+), the actual project. **All Laravel commands below run from `app/`.**
- `edge/` — a Caddy reverse-proxy stack (`docker-compose.yml` + `Caddyfile`) that terminates TLS and forwards to `app` on a shared external Docker network called `edge`. Deployed separately, once per VPS, from `/srv/edge`.
- `docs/openapi.json`, `docs/README-laravel.md`, `docs/README-laravel-security.md` — the spec for what this API must implement (see "Purpose & spec" below).
- `README-deploy-ujian.md` (root) / `docs/README-deploy-ujian.md` — VPS deployment runbook (Docker Compose + FrankenPHP + Caddy + MySQL + Redis).

## Purpose & spec

This is a REST API backend used as the grading target for a "Pemrograman Desktop" (desktop programming — VB/WPF/C#/Java/Python clients) exam. Students build a desktop GUI client that authenticates and does CRUD against one of four independent "paket" (packages), all backed by the same Laravel app:

- **Paket 1** — Tasks (`/api/tasks`) — `App\Models\Paket1\Task`, `App\Http\Controllers\Api\Paket1\TaskController`
- **Paket 2** — Attendances (`/api/attendances`) — `App\Models\Paket2\Attendance`, `App\Http\Controllers\Api\Paket2\AttendanceController`
- **Paket 3** — Menus (`/api/menus`) — `App\Models\Paket3\Menu`, `App\Http\Controllers\Api\Paket3\MenuController`
- **Paket 4** — Tickets (`/api/tickets`) — `App\Models\Paket4\Ticket`, `App\Http\Controllers\Api\Paket4\TicketController`

Every package's model/controller/migration follows the exact same shape (see `docs/README-laravel.md` for the full field spec of each table and `docs/openapi.json` for the formal OpenAPI contract). When adding a field or endpoint to one package, check whether the same change is expected in the other three — they are meant to stay structurally parallel, not to diverge.

**The JSON response envelope is a hard contract, not a convention** — desktop clients parse it exactly as documented in `docs/README-laravel.md`:
- Single object: `{ "code", "status", "body" }`
- List/paginated: `{ "code", "status", "body": [...], "page": { "size", "totalPage", "total", "current" } }`
- Error: `{ "code", "status", "errors": { field: [messages] } }`

Always build responses via `App\Traits\ApiResponse` (`successResponse()`, `paginatedResponse()`, `errorResponse()`) rather than hand-rolling `response()->json(...)` — every controller uses this trait through `App\Http\Controllers\Api\Controller`. `date`/`due_date`-type fields are validated and cast as plain strings in `d-m-Y` format (not Carbon dates) to match the desktop clients' expected format.

## Auth model

Laravel Sanctum, personal access tokens (bearer token), not SPA cookie auth:
- `POST /api/auth/register`, `POST /api/auth/login` — public, `throttle:60,1` (keyed by IP — unauthenticated).
- Everything else (`auth/logout`, `auth/me`, and all four `apiResource` groups) — `auth:sanctum` + `throttle:env('API_RATE_LIMIT', 300),1`, see `routes/api.php`. Because `auth:sanctum` runs before `throttle`, this is keyed per authenticated user (`ThrottleRequests::resolveRequestSignature()` uses `$request->user()->getAuthIdentifier()`), not per IP — so a classroom sharing one NAT'd IP doesn't share one throttle bucket.
- Token expiration is set in `config/sanctum.php` (`expiration` in minutes) — intentionally short-lived to match an exam session.
- All four CRUD resources are scoped to the authenticated user: every table (`tasks`, `attendances`, `menus`, `tickets`) has a `user_id` FK, `store()` stamps it server-side from `$request->user()->id` (never trust the request body for it), and `index`/`show`/`update`/`destroy` all filter by `where('user_id', $request->user()->id)`. A record owned by another user returns `404 not_found` (not `403`) to avoid leaking existence. Covered by `tests/Feature/ApiEndpointTest.php::test_data_isolation_between_users`.

## Common commands

Run from `app/`:

```bash
# Install & bootstrap (fresh clone)
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Local dev server (serves app + queue listener + logs + vite together)
composer run dev

# Tests (uses in-memory SQLite, see phpunit.xml)
composer run test
php artisan test
php artisan test --filter=test_auth_endpoints   # single test method
php artisan test tests/Feature/ApiEndpointTest.php

# Lint / format
vendor/bin/pint          # Laravel Pint (PSR-12-ish), fixes in place
vendor/bin/pint --test   # check only, no changes

# Tinker / one-off DB queries
php artisan tinker

# Swagger UI (wotz/laravel-swagger-ui, reads docs/openapi.json)
# check config/swagger-ui.php or routes for the served path
```

Tests run against `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` (configured in `phpunit.xml`), independent of whatever `.env` points at — no DB setup needed to run the suite. Feature tests live in `tests/Feature/`, use `RefreshDatabase`, and authenticate via a helper that creates a `User`, mints a Sanctum token, and returns Bearer/Accept headers (see `tests/Feature/ApiEndpointTest.php::authenticate()`).

## Docker / deployment

Production target is a single VPS running: Caddy (edge, only container with published ports) → `app` (FrankenPHP, Laravel, internal port 8080 only) → MySQL 8.4 + Redis 7 (both on an `internal` network with no published ports — never add `ports:` to `db` or `redis` in `docker-compose.yml`). Full rationale and setup steps are in `README-deploy-ujian.md`.

- `Dockerfile` — multi-stage: `composer:2` for vendor install, then `dunglas/frankenphp:1-php8.4` runtime, runs as non-root `app` user, `HEALTHCHECK` hits `/up`.
- `docker-compose.yml` (in `app/`) — defines `app`, `db`, `redis`, plus opt-in `worker`/`scheduler` behind the `jobs` Compose profile (`docker compose --profile jobs up -d`).
- `deploy.sh` — `git pull --ff-only` → `docker compose build` → `docker compose up -d --remove-orphans` → health-check `/up`. This is the standard update path on the VPS; don't hand-roll deploy steps that skip it.
- `backup-db.sh` (repo root) — daily MySQL dump job, invoked via crontab on the VPS, not part of the app runtime.
- `bootstrap/app.php` configures `trustProxies` implicitly expected (see `README-deploy-ujian.md` §6.1) — required so `url()`/`$request->ip()` are correct behind Caddy; don't remove trust-proxy config if present.
- API rate limiting is deliberately generous (`throttle:300,1` on CRUD) because an entire classroom can share one NAT'd school IP — don't tighten this to a typical per-IP default without checking that context first.

## Editing conventions specific to this repo

- New CRUD endpoints go through `Validator::make(...)` + manual `if ($validator->fails())` → `errorResponse()`, not Form Request classes with automatic 422 responses — this repo standardizes on 400-with-custom-body for validation errors to match the documented envelope (see `docs/README-laravel.md` §"Standar Format Response JSON API"), not Laravel's default 422 shape.
- Model lookups use `Model::find($id)` + manual 404 via `errorResponse(..., 404, 'not_found')`, not route-model binding / `findOrFail()` — keeps the error body in the standard envelope instead of Laravel's default exception JSON.
- `$fillable` must be defined explicitly on every model (mass-assignment protection is called out explicitly in `docs/README-laravel-security.md`).

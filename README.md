# Wardtech Laravel Connector

A lightweight, standalone Laravel package that reports an application's status to
the Wardtech central dashboard at **apps.wardtech.co.uk**.

It is intentionally simple and safe to open-source: the only secret it ever
handles is your `WARDTECH_TOKEN`, which lives in `.env` and is never committed.

## What it reports

| Signal | Detail |
| --- | --- |
| **Laravel version** | `app()->version()` |
| **Installed packages** | Every package + exact version from `composer.lock`. The dashboard correlates these against the security-advisory database to surface **CVE warnings** — so no `composer` binary is needed on the client and the CVE logic lives in one place you control. |
| **Queue status** | Default connection, driver, pending job count and failed job count (best-effort per driver). |
| **Environment posture** | `env`, `APP_DEBUG` state, maintenance mode, and config/route/event cache flags. `debug=true` in production is a high-value flag. |
| **PHP & server** | PHP version, OS, hostname, SAPI, memory limit, loaded extensions. Spot EOL PHP across the fleet. |
| **Migrations** | Count of run vs. pending migrations — flags half-deployed apps. |
| **Scheduler heartbeat** | When this app last successfully reported, so you can detect apps whose cron has silently died. |

### What it does **not** send

No `APP_KEY`, no database credentials, no `.env` values, no user data. Only the
non-sensitive operational signals listed above.

## Installation

This package is hosted on GitHub, not Packagist, so add it as a VCS repository
in your application's `composer.json` first:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/WardTech-Consultants/laravel-apps-connector.git"
    }
]
```

Then require it:

```bash
composer require wardtech/laravel-connector
```

> If the repository is private, Composer needs a GitHub token to read it:
> `composer config --global github-oauth.github.com <your-token>`.
>
> To track the unreleased `main` branch instead of a tagged release, require
> `wardtech/laravel-connector:dev-main`.

Publish the config (optional — sensible defaults ship with the package):

```bash
php artisan vendor:publish --tag=wardtech-config
```

Add your token to `.env`:

```dotenv
WARDTECH_TOKEN=your-token-here
# WARDTECH_URL=https://apps.wardtech.co.uk   # override only if needed
```

## Usage

Verify connectivity and credentials:

```bash
php artisan wardtech:ping
# → Connected to Wardtech as: My Application
```

Send a report on demand:

```bash
php artisan wardtech:report
```

Preview the exact payload without sending anything:

```bash
php artisan wardtech:report --dry-run
```

## Scheduling

Once installed, the package automatically registers a daily report on Laravel's
scheduler (default `03:00`) — you only need the framework scheduler running:

```
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

Disable the auto-schedule (e.g. to trigger from a deploy hook instead) via
`WARDTECH_SCHEDULE=false`, or change the time with `WARDTECH_SCHEDULE_TIME=02:30`.

## Configuration reference

| Env var | Default | Purpose |
| --- | --- | --- |
| `WARDTECH_TOKEN` | `null` | Bearer token (required to send). |
| `WARDTECH_URL` | `https://apps.wardtech.co.uk` | Central server base URL. |
| `WARDTECH_ENABLED` | `true` | Master on/off switch. |
| `WARDTECH_TIMEOUT` | `15` | HTTP timeout in seconds. |
| `WARDTECH_SCHEDULE` | `true` | Register the daily scheduled report. |
| `WARDTECH_SCHEDULE_TIME` | `03:00` | Time of day for the scheduled report. |

## Payload shape

`POST /api/status-report`

```json
{
  "type": "laravel",
  "core_version": "11.9.0",
  "reported_at": "2026-07-08T03:00:00+00:00",
  "application": { "name": "My App", "url": "https://myapp.com", "locale": "en", "timezone": "UTC" },
  "environment": { "env": "production", "debug": false, "maintenance_mode": false, "config_cached": true, "routes_cached": true, "events_cached": false },
  "server": { "php_version": "8.3.6", "os": "Linux 6.5.0", "hostname": "web-01", "sapi": "fpm-fcgi", "memory_limit": "512M", "max_execution_time": "30", "extensions": ["Core", "..."] },
  "components": [ { "name": "laravel/framework", "version": "v11.9.0", "active": true } ],
  "queue": { "default_connection": "redis", "driver": "redis", "pending_jobs": 3, "failed_jobs": 0 },
  "migrations": { "status": "ok", "ran": 42, "pending": 0, "pending_migrations": [] },
  "schedule": { "schedule_enabled": true, "last_reported_at": "2026-07-07T03:00:01+00:00" }
}
```

## Extending

Report collectors implement `Wardtech\Connector\Contracts\Collector` and are
listed in `Wardtech\Connector\StatusReport`. A failure in any single collector
is isolated and replaced with an `{ "error": "..." }` marker, so one broken
signal never blocks the whole report.

## License

MIT

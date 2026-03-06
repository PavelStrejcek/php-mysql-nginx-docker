# PHP 8.4 FPM + Nginx (latest) + PostgreSQL 18 — Docker Compose starter

This repository provides a ready-to-run Docker environment for a PHP web application using:
- PHP 8.4 FPM (Alpine)
- Nginx (stable-alpine)
- PostgreSQL 18 (Alpine)

It is framework-agnostic and points the web root to `src/public` by default.

## Quick start

1) Copy environment file and adjust if needed:

```
cp .env.example .env
```

2) Build and start the stack:

```
docker compose up -d --build
```

3) Open the app in your browser:

- http://localhost:8080 (or the port you set in `APP_PORT`)

You should see a simple PHP page, and (once PostgreSQL is ready) database connectivity information.

## Project structure

```
.
├─ docker-compose.yml
├─ .env.example (copy to .env)
├─ docker/
│  ├─ php/
│  │  └─ Dockerfile
│  └─ nginx/
│     └─ default.conf
└─ src/
   └─ public/
      └─ index.php
```

- Web root (document root): `src/public`
- PHP container working dir: `/var/www/html`

## Service details

### PHP (FPM)
- Image: `php:8.4-fpm-alpine` (built locally with common extensions)
- Extensions enabled: `pdo_pgsql`, `intl`, `mbstring`, `zip`, `opcache`
- Composer is available inside the container (`/usr/bin/composer`).

Run Composer inside the PHP container (recommended for consistent platform):

```
docker compose exec php composer --version
# or install deps
# docker compose exec php composer install
```

### Nginx
- Image: `nginx:stable-alpine`
- Serves `src/public` and forwards `*.php` to `php:9000` (FPM).
- Default vhost config at `docker/nginx/default.conf`.

### PostgreSQL 18
- Image: `postgres:18.3-alpine`
- Data persisted in named volume `postgres_data`.
- Connection from PHP: host `postgres`, port `5432`, credentials from `.env`/`docker-compose.yml`.

Connect to PostgreSQL from host:

```
docker compose exec postgres psql -U $POSTGRES_USER -d $POSTGRES_DB
```

## Environment variables

- `APP_PORT` (default: `8080`) — published HTTP port
- `POSTGRES_DB` (default: `app`)
- `POSTGRES_USER` (default: `app`)
- `POSTGRES_PASSWORD` (default: `app`)
- `POSTGRES_PORT` (default: `5432`)

Set them in `.env` or edit `docker-compose.yml` directly.

## Notes

- The Nginx config expects your application's public files (including `index.php`) in `src/public`.
- For Laravel/Symfony and similar frameworks, point the document root here without modifications.
- The PostgreSQL container has a healthcheck; the PHP page may show DB connection errors briefly until PostgreSQL is ready — this is normal on first startup.
- If you need more PHP extensions, edit `docker/php/Dockerfile` and rebuild: `docker compose build php && docker compose up -d`.

## Tear down

Stop and remove containers (data volume preserved):

```
docker compose down
```

Remove containers and the PostgreSQL data volume:

```
docker compose down -v
```

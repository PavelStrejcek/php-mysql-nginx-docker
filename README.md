# PHP 8.5 FPM + Apache HTTPD 2.4 (mpm_event) + MySQL 8.4 — Docker Compose starter

This repository provides a ready-to-run Docker environment for a PHP web application using:

- PHP 8.5 FPM (Alpine)
- Apache HTTP Server 2.4 (httpd:alpine, mpm_event)
- MySQL 8.4

It is framework-agnostic and points the web root to `src/public` by default.

## Quick start

1. Copy environment file and adjust if needed:

```
cp .env.example .env
```

2. Build and start the stack:

```
docker compose up -d --build
```

3. Open the app in your browser:

- http://localhost:8080 (or the port you set in `APP_PORT`)

You should see a simple PHP page, and (once MySQL is ready) database connectivity information.

## Project structure

```
.
├─ docker-compose.yml
├─ .env.example (copy to .env)
├─ docker/
│  ├─ php/
│  │  └─ Dockerfile
│  └─ apache/
│     └─ httpd.conf
└─ src/
   └─ public/
      └─ index.php
```

- Web root (document root): `src/public`
- PHP container working dir: `/var/www/html`

## Service details

### PHP (FPM)

- Image: `php:8.4-fpm-alpine` (built locally with common extensions)
- Extensions enabled: `pdo_mysql`, `intl`, `mbstring`, `zip`
- Composer is available inside the container (`/usr/bin/composer`).

Run Composer inside the PHP container (recommended for consistent platform):

```
docker compose exec php composer --version
# or install deps
# docker compose exec php composer install
```

### Apache HTTPD 2.4

- Image: `httpd:2.4-alpine`
- Runs with the event MPM and serves `src/public`.
- Forwards `*.php` to the `php` service (PHP-FPM) via `proxy_fcgi` on `php:9000`.
- Main config mounted at `docker/apache/httpd.conf` (allows `.htaccess` via `AllowOverride All`).

### MySQL 8.4

- Image: `mysql:8.4`
- Data persisted in named volume `mysql_data`.
- Connection from PHP: host `mysql`, port `3306`, credentials from `.env`/`docker-compose.yml`.

Connect to MySQL from host:

```
docker compose exec mysql mysql -u$MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE
```

## Environment variables

- `APP_PORT` (default: `8080`) — published HTTP port
- `MYSQL_DATABASE` (default: `app`)
- `MYSQL_USER` (default: `app`)
- `MYSQL_PASSWORD` (default: `app`)
- `MYSQL_ROOT_PASSWORD` (default: `root`)
- `MYSQL_PORT` (default: `3306`)

Set them in `.env` or edit `docker-compose.yml` directly.

## Notes

- The Apache config expects your application's public files (including `index.php`) in `src/public`.
- `.htaccess` is allowed by default. For central config-only setups, put your rewrites in `docker/apache/httpd.conf` and change `AllowOverride None`.
- The MySQL container has a healthcheck; the PHP page may show DB connection errors briefly until MySQL is ready — this is normal on first startup.
- If you need more PHP extensions, edit `docker/php/Dockerfile` and rebuild: `docker compose build php && docker compose up -d`.
- The old Nginx config remains in the repo (`docker/nginx/default.conf`) for reference; it's no longer used by Compose.

## Tear down

Stop and remove containers (data volume preserved):

```
docker compose down
```

Remove containers and the MySQL data volume:

```
docker compose down -v
```

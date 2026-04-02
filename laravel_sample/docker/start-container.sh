#!/bin/sh
set -eu

if [ "${FCGI_CAPTURE_AUTO:-false}" = "true" ]; then
    /usr/local/bin/fastcgi-capture-start
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -z "${APP_KEY:-}" ]; then
    php artisan key:generate --force
fi

until pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" >/dev/null 2>&1; do
    sleep 1
done

php artisan migrate --force

exec php-fpm

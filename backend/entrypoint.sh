#!/bin/sh
set -e

composer install --no-interaction

if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan key:generate --force
php artisan migrate --force || true

php artisan serve --host=0.0.0.0 --port=8000

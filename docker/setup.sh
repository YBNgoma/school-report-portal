#!/bin/sh
set -eu
[ -f .env ] || cp .env.example .env
composer install --no-interaction --prefer-dist
grep -q '^APP_KEY=.\+' .env || php artisan key:generate --force
php artisan storage:link --force
php artisan migrate --force
if [ -d database/seeders ] && [ "${SEED:-1}" = "1" ] && [ "${APP_ENV:-local}" != "production" ]; then php artisan db:seed --force; fi

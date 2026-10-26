#!/usr/bin/env bash
set -e

cd /app

if [ ! -f .env ]; then
    cp .env.example .env
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache database
chmod -R 777 storage bootstrap/cache database 2>/dev/null || true

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/app/database/database.sqlite}"
    touch "$DB_FILE"
    chmod 666 "$DB_FILE" 2>/dev/null || true
fi

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --ansi
fi

php artisan migrate --force --ansi

# Seed initial master & demo data if database is empty
ROLE_COUNT=$(php artisan tinker --execute="echo \App\Models\Role::count();" 2>/dev/null | tail -n 1 | tr -dc '0-9')
if [ "${ROLE_COUNT:-0}" = "0" ]; then
    php artisan db:seed --force --ansi
fi

PORT="${PORT:-8080}"
echo "Starting SIDISURAT on 0.0.0.0:${PORT}..."
exec php -S "0.0.0.0:${PORT}" -t public

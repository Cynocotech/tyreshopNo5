#!/bin/bash
set -e

cd /var/www/html/admin

# Create .env from example if missing
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Write env vars from Docker environment into .env
# (Dokploy injects these as environment variables)
[ -n "$APP_KEY"   ] && sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
[ -n "$APP_URL"   ] && sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
[ -n "$APP_ENV"   ] && sed -i "s|^APP_ENV=.*|APP_ENV=${APP_ENV}|" .env
[ -n "$APP_DEBUG" ] && sed -i "s|^APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG}|" .env

# Generate key if still blank
grep -q "^APP_KEY=$" .env && php artisan key:generate --force || true

# Ensure SQLite database file exists
mkdir -p database
touch database/database.sqlite

# Run migrations
php artisan migrate --force

# Cache for production speed
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"

#!/bin/sh
set -e

if [ ! -f ".env" ] && [ -f ".env.example" ]; then
  cp .env.example .env
  echo ".env created from .env.example (please update values)"
fi

echo "Waiting for MySQL..."
until php -r "try { new PDO('mysql:host=db;dbname=coprra', 'coprra', 'secret'); echo 'DB OK'; } catch (Exception $e) { exit(1); }" 2>/dev/null; do
  sleep 2
done

composer install --no-interaction || true
php artisan key:generate --force || true
php artisan migrate --force || true

exec "$@"

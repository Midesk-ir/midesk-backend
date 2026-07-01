#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
  cp .env.example .env
fi

composer install --no-interaction
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force

echo "Midesk installation complete."

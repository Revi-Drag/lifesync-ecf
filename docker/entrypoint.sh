#!/usr/bin/env sh
set -e

echo "[entrypoint] Waiting for database..."
# On attend que Doctrine puisse se connecter (jusqu'à 60s)
for i in $(seq 1 30); do
  if php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; then
    echo "[entrypoint] Database is up."
    break
  fi
  echo "[entrypoint] DB not ready yet ($i/30) - sleeping 2s..."
  sleep 2
done

echo "[entrypoint] Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction || true

echo "[entrypoint] Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf

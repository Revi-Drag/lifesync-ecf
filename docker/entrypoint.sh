#!/usr/bin/env sh
set -e

echo "[entrypoint] Waiting for database..."
READY=0

for i in $(seq 1 30); do
  if php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; then
    echo "[entrypoint] Database is up."
    READY=1
    break
  fi
  echo "[entrypoint] DB not ready yet ($i/30) - sleeping 2s..."
  sleep 2
done

if [ "$READY" -ne 1 ]; then
  echo "[entrypoint] Database still not reachable after 60s. Exiting."
  exit 1
fi

echo "[entrypoint] Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "[entrypoint] Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf

#!/usr/bin/env sh
set -e

echo "[entrypoint] APP_ENV=$APP_ENV APP_DEBUG=$APP_DEBUG"

# Affiche DATABASE_URL sans le password
if [ -n "$DATABASE_URL" ]; then
  echo "[entrypoint] DATABASE_URL set: $(echo "$DATABASE_URL" | sed -E 's#(://[^:]+:)[^@]+@#\1****@#')"
else
  echo "[entrypoint] DATABASE_URL is EMPTY"
fi

echo "[entrypoint] Waiting for database..."

# Attente jusqu'à 120s (60 * 2s)
for i in $(seq 1 60); do
  if php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; then
    echo "[entrypoint] Database is up."
    break
  fi
  echo "[entrypoint] DB not ready yet ($i/60) - sleeping 2s..."
  sleep 2
done

# Si DB ok -> migrations ; sinon on démarre quand même (pas de crash-loop)
if php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; then
  echo "[entrypoint] Running migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction || true
else
  echo "[entrypoint] Database still not reachable after 120s. Starting anyway (no migrations)."
fi

echo "[entrypoint] Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf

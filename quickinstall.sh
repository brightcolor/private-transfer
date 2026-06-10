#!/usr/bin/env sh
set -eu

APP_ROOT="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
DATA_DIR="${PRIVATE_TRANSFER_DATA_DIR:-/opt/private-transfer}"
STORAGE_DIR="${PRIVATE_TRANSFER_STORAGE_DIR:-$DATA_DIR/storage}"
POSTGRES_DIR="${PRIVATE_TRANSFER_POSTGRES_DIR:-$DATA_DIR/postgres}"
APP_URL="${APP_URL:-http://localhost:8080}"

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is required but was not found in PATH." >&2
    exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
    echo "Docker Compose v2 is required but was not found." >&2
    exit 1
fi

SUDO=""
if [ "$(id -u)" -ne 0 ]; then
    if ! command -v sudo >/dev/null 2>&1; then
        echo "sudo is required to create directories below $DATA_DIR." >&2
        exit 1
    fi
    SUDO="sudo"
fi

echo "Creating host directories in $DATA_DIR"
$SUDO mkdir -p \
    "$STORAGE_DIR/app" \
    "$STORAGE_DIR/framework/cache" \
    "$STORAGE_DIR/framework/sessions" \
    "$STORAGE_DIR/framework/views" \
    "$STORAGE_DIR/logs" \
    "$POSTGRES_DIR"

$SUDO chmod -R u+rwX,g+rwX "$DATA_DIR"
$SUDO chown -R 82:82 "$STORAGE_DIR"

if [ ! -f "$APP_ROOT/.env" ]; then
    cp "$APP_ROOT/.env.example" "$APP_ROOT/.env"
fi

set_env() {
    key="$1"
    value="$2"
    file="$APP_ROOT/.env"

    if grep -q "^$key=" "$file"; then
        sed -i.bak "s|^$key=.*|$key=$value|" "$file"
    else
        printf '\n%s=%s\n' "$key" "$value" >> "$file"
    fi
}

set_env APP_URL "$APP_URL"
set_env DB_CONNECTION pgsql
set_env DB_HOST postgres
set_env DB_PORT 5432
set_env DB_DATABASE private_transfer
set_env DB_USERNAME private_transfer
set_env DB_PASSWORD private_transfer
set_env REDIS_HOST redis
set_env QUEUE_CONNECTION redis
set_env CACHE_STORE redis
set_env SESSION_DRIVER redis
rm -f "$APP_ROOT/.env.bak"

export PRIVATE_TRANSFER_STORAGE_DIR="$STORAGE_DIR"
export PRIVATE_TRANSFER_POSTGRES_DIR="$POSTGRES_DIR"

cd "$APP_ROOT"

docker compose build
docker compose up -d postgres redis
docker compose up -d

if ! grep -Eq '^APP_KEY=base64:.+' "$APP_ROOT/.env"; then
    docker compose exec -T app php artisan key:generate --force
fi

docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize:clear

echo "Private Transfer is running at $APP_URL"

#!/usr/bin/env sh
set -eu

APP_ROOT="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
DATA_DIR="${PRIVATE_TRANSFER_DATA_DIR:-/opt/private-transfer}"
INSTALL_DIR="${PRIVATE_TRANSFER_INSTALL_DIR:-$DATA_DIR/app}"
STORAGE_DIR="${PRIVATE_TRANSFER_STORAGE_DIR:-$DATA_DIR/storage}"
POSTGRES_DIR="${PRIVATE_TRANSFER_POSTGRES_DIR:-$DATA_DIR/postgres}"
APP_URL="${APP_URL:-}"

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is required but was not found in PATH." >&2
    exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
    echo "Docker Compose v2 is required but was not found." >&2
    exit 1
fi

if ! command -v curl >/dev/null 2>&1; then
    echo "curl is required but was not found in PATH." >&2
    exit 1
fi

if ! command -v tar >/dev/null 2>&1; then
    echo "tar is required but was not found in PATH." >&2
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

is_app_root() {
    dir="$1"
    [ -f "$dir/docker-compose.yml" ] && [ -f "$dir/.env.example" ] && [ -f "$dir/artisan" ]
}

download_source() {
    target="$1"
    tmp_dir="$(mktemp -d)"

    echo "Downloading Private Transfer into $target"
    $SUDO mkdir -p "$target"
    curl -fsSL https://github.com/brightcolor/private-transfer/archive/refs/heads/main.tar.gz \
        | tar -xz -C "$tmp_dir" --strip-components=1
    $SUDO cp -R "$tmp_dir/." "$target/"
    rm -rf "$tmp_dir"
}

if ! is_app_root "$APP_ROOT"; then
    download_source "$INSTALL_DIR"
    APP_ROOT="$INSTALL_DIR"
elif [ "$APP_ROOT" = "$INSTALL_DIR" ]; then
    download_source "$INSTALL_DIR"
fi

echo "Creating host directories in $DATA_DIR"
$SUDO mkdir -p \
    "$INSTALL_DIR" \
    "$STORAGE_DIR/app" \
    "$STORAGE_DIR/framework/cache" \
    "$STORAGE_DIR/framework/sessions" \
    "$STORAGE_DIR/framework/views" \
    "$STORAGE_DIR/logs" \
    "$POSTGRES_DIR"

$SUDO chmod -R u+rwX,g+rwX "$DATA_DIR"

if [ "$(id -u)" -ne 0 ]; then
    $SUDO chown -R "$(id -u):$(id -g)" "$INSTALL_DIR"
fi

$SUDO chown -R 82:82 "$STORAGE_DIR"

ENV_CREATED=false

if [ ! -f "$APP_ROOT/.env" ]; then
    cp "$APP_ROOT/.env.example" "$APP_ROOT/.env"
    ENV_CREATED=true
fi

get_env() {
    key="$1"
    file="$APP_ROOT/.env"
    line="$(grep -E "^$key=" "$file" | tail -n 1 || true)"

    if [ -z "$line" ]; then
        return 1
    fi

    printf '%s\n' "${line#*=}"
}

port_is_used() {
    port="$1"

    if command -v ss >/dev/null 2>&1; then
        ss -ltn | awk '{print $4}' | grep -Eq "[:.]$port$"
        return $?
    fi

    if command -v lsof >/dev/null 2>&1; then
        lsof -iTCP:"$port" -sTCP:LISTEN -Pn >/dev/null 2>&1
        return $?
    fi

    if command -v netstat >/dev/null 2>&1; then
        netstat -ltn | awk '{print $4}' | grep -Eq "[:.]$port$"
        return $?
    fi

    return 1
}

random_port() {
    echo $((20000 + ($(date +%s) % 40000)))
}

find_free_port() {
    candidate="$(random_port)"
    tries=0

    while [ "$tries" -lt 200 ]; do
        if [ "$candidate" -gt 60999 ]; then
            candidate=20000
        fi

        if ! port_is_used "$candidate"; then
            printf '%s\n' "$candidate"
            return 0
        fi

        candidate=$((candidate + 1))
        tries=$((tries + 1))
    done

    echo "Could not find a free HTTP port." >&2
    exit 1
}

HTTP_PORT_SOURCE="default"
HTTP_PORT="${PRIVATE_TRANSFER_HTTP_PORT:-}"

if [ -n "$HTTP_PORT" ]; then
    HTTP_PORT_SOURCE="env"
else
    HTTP_PORT="$(get_env PRIVATE_TRANSFER_HTTP_PORT || true)"

    if [ -n "$HTTP_PORT" ] && [ "$ENV_CREATED" = "false" ]; then
        HTTP_PORT_SOURCE="file"
    else
        HTTP_PORT=8080
    fi
fi

if [ "$HTTP_PORT_SOURCE" = "default" ] && port_is_used "$HTTP_PORT"; then
    echo "Port $HTTP_PORT is already in use; selecting a free random port."
    HTTP_PORT="$(find_free_port)"
fi

if [ "$HTTP_PORT_SOURCE" = "env" ] && port_is_used "$HTTP_PORT"; then
    echo "Configured HTTP port $HTTP_PORT is already in use." >&2
    exit 1
fi

if [ -z "$APP_URL" ]; then
    APP_URL="http://localhost:$HTTP_PORT"
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

generate_app_key() {
    random="$(dd if=/dev/urandom bs=32 count=1 2>/dev/null | base64 | tr -d '\n')"
    printf 'base64:%s\n' "$random"
}

APP_KEY_VALUE="$(get_env APP_KEY || true)"

if ! printf '%s' "$APP_KEY_VALUE" | grep -Eq '^base64:.+'; then
    set_env APP_KEY "$(generate_app_key)"
fi

set_env APP_URL "$APP_URL"
set_env PRIVATE_TRANSFER_HTTP_PORT "$HTTP_PORT"
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
export PRIVATE_TRANSFER_HTTP_PORT="$HTTP_PORT"

cd "$APP_ROOT"

dc() {
    docker compose -f "$APP_ROOT/docker-compose.prod.yml" "$@"
}

dc pull
dc up -d postgres redis

echo "Waiting for PostgreSQL to be ready."
tries=0

until dc exec -T postgres pg_isready -U private_transfer >/dev/null 2>&1; do
    tries=$((tries + 1))

    if [ "$tries" -gt 30 ]; then
        echo "PostgreSQL did not become ready in time." >&2
        dc ps
        exit 1
    fi

    sleep 2
done

dc up -d --force-recreate app worker scheduler nginx

echo "Waiting for the application container to be ready."
tries=0

until dc exec -T app php artisan --version >/dev/null 2>&1; do
    tries=$((tries + 1))

    if [ "$tries" -gt 30 ]; then
        echo "The application container did not become ready in time." >&2
        dc ps
        exit 1
    fi

    sleep 2
done

dc exec -T app php artisan migrate --force
dc exec -T app php artisan optimize:clear

echo "Private Transfer is running at $APP_URL"

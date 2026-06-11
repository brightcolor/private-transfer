# Private Transfer

Private Transfer is a self-hostable Laravel application for privacy-friendly file transfers. It provides a WeTransfer-like flow without tracking scripts, public storage URLs, external fonts, or account management.

The first version was intentionally built fast with AI-assisted coding, so treat `v0.1.0` as a usable foundation that still deserves a careful production review before handling sensitive workloads.

## What It Does

- Lets a sender choose files, enter a recipient email, and start a transfer.
- Uploads files in resumable chunks while the browser tab remains open.
- Sends the download link automatically after every file is complete.
- Provides tokenized download pages, individual downloads, and ZIP downloads.
- Supports optional sender copy, sender message, password protection, download limits, and automatic expiry.
- Supports per-transfer retention selection up to the configured maximum.
- Shows upload progress with speed, ETA, pause/resume, and a completion page with a copyable download link.
- Copy-to-clipboard uses the browser Clipboard API; production deployments should use HTTPS for reliable support.
- Can notify the sender after the first successful download when the sender email and option are set.
- Shows queued/sent mail status on the completion page.
- Stores only the data required to operate a transfer.

## What It Does Not Do

- It does not guarantee uploads continue after the tab or browser is closed. Browsers do not provide a reliable general-purpose background upload API.
- It does not include user accounts or a full admin dashboard in `0.1.0`.
- It does not make the operator GDPR-compliant by itself. The operator remains responsible for a DPA, TOMs, privacy notices, retention policy, and lawful mail processing.

## Architecture

- Laravel 13, PHP 8.3+, Blade, Tailwind CSS, and minimal vanilla JavaScript.
- PostgreSQL for production, SQLite can be used locally.
- Redis-backed queues and cache.
- SMTP through Laravel Mail.
- Local private storage for development and S3-compatible storage for production.
- Chunk data is written to private local temporary storage, then completed files are moved to the configured filesystem disk.
- Queue jobs are idempotent and send mail only after the transfer is complete.

## Local Docker Start

For a first-time Docker install on a Linux host:

```bash
chmod +x quickinstall.sh
./quickinstall.sh
```

For a copy-paste install from GitHub:

```bash
curl -fsSL "https://raw.githubusercontent.com/brightcolor/private-transfer/main/quickinstall.sh?$(date +%s)" -o quickinstall.sh && chmod +x quickinstall.sh && ./quickinstall.sh
```

The installer creates persistent bind-mount directories below `/opt/private-transfer` by default. Override them with `PRIVATE_TRANSFER_DATA_DIR`, `PRIVATE_TRANSFER_STORAGE_DIR`, or `PRIVATE_TRANSFER_POSTGRES_DIR` before running the script.
When launched through the curl command, it downloads the application source into `/opt/private-transfer/app`. Override that location with `PRIVATE_TRANSFER_INSTALL_DIR`.
It uses HTTP port `8080` by default. If that port is already in use during a first-time install, the script selects a free random high port and writes it to `.env` as `PRIVATE_TRANSFER_HTTP_PORT`.

Manual Docker start:

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
```

Open `http://localhost:8080`.

Docker is expected to run app, Nginx, PostgreSQL, Redis, worker, and scheduler services.
The PHP services build the `app` Docker stage, while the HTTP service builds the separate `web` Nginx stage.
The host HTTP port is controlled by `PRIVATE_TRANSFER_HTTP_PORT`, defaulting to `8080`.
Persistent Docker data is stored through host bind mounts, defaulting to `/opt/private-transfer/storage` and `/opt/private-transfer/postgres`.

## Required ENV Values

Set these before production use:

- `APP_KEY`, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`
- `DB_*` for PostgreSQL
- `REDIS_*`
- `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`
- `FILESYSTEM_DISK=s3` plus `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT` if using S3-compatible storage
- `TRANSFER_RETENTION_DAYS`, `TRANSFER_MAX_UPLOAD_MB`, `TRANSFER_CHUNK_SIZE_MB`
- `TRANSFER_ALLOWED_MIME_TYPES` if uploads should be restricted

## Operations

Run cleanup manually:

```bash
php artisan transfers:cleanup
```

Show storage statistics:

```bash
php artisan transfers:stats
```

Health endpoints:

- `/health` returns a simple liveness response.
- `/ready` checks database, cache, and storage readiness.

## CI And Releases

`ci.yml` installs Composer and npm dependencies, builds assets, runs tests, runs PHP linting, and builds the Docker image. `docker.yml` builds on `main` and pushes GHCR images for SemVer tags such as `v0.1.0`.

Semantic Versioning is used:

- `MAJOR` for breaking changes.
- `MINOR` for backward-compatible features.
- `PATCH` for backward-compatible fixes.

Use Conventional Commits such as `feat: add transfer expiry cleanup` or `fix: reject stale chunk offsets`.

## Browser Upload Limits

Uploads continue while the browser tab is open, including when it is in the background. If the page reloads or the connection drops, the client can ask the server for the saved chunk offset and resume after the same files are selected again. If the tab or browser is closed, continuation is not guaranteed because the browser may terminate JavaScript execution and discard file handles.

## Troubleshooting

- `413 Payload Too Large`: increase Nginx `client_max_body_size`, PHP `upload_max_filesize`, and `post_max_size`.
- Mail not sent: verify the worker is running and SMTP credentials are valid.
- Downloads fail on S3: verify `FILESYSTEM_DISK=s3`, endpoint settings, bucket permissions, and private object access.
- Upload cannot resume after reload: select the same files again so the browser can provide file handles.

See the `docs/` directory for architecture, privacy, security, and deployment notes.

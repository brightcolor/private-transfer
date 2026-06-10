# Deployment

## Production Checklist

1. Set `APP_ENV=production`, `APP_DEBUG=false`, and a strong `APP_KEY`.
2. Use PostgreSQL and Redis with backups and monitoring.
3. Configure SMTP and verify queue workers.
4. Use S3-compatible private storage for production-scale files.
5. Run `php artisan migrate --force` during deployment.
6. Run `php artisan config:cache` and `php artisan route:cache`.
7. Run a scheduler every minute or use the provided scheduler service.
8. Put TLS in front of Nginx.

## Backups

Back up PostgreSQL and the configured storage disk together. Restoring only one side can leave orphaned metadata or inaccessible files.

## Updates

Read `CHANGELOG.md`, back up database and storage, deploy the new image, run migrations, and monitor queues and `/ready`.

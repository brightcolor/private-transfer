# Privacy

Private Transfer follows privacy by design principles, but the operator remains responsible for legal compliance.

## Data Stored

- Recipient email address.
- Optional sender email address.
- Optional message.
- Original filenames as metadata.
- File size, MIME type, storage path, checksum, transfer state, expiry, and download counters.

The application does not add analytics, tracking scripts, external fonts, or unnecessary cookies.

## Retention

Transfers expire automatically after `TRANSFER_RETENTION_DAYS`. Run the scheduler or `php artisan transfers:cleanup` to delete expired files and metadata.

## GDPR Notes

Operators must provide their own privacy notice, data processing agreements, technical and organizational measures, backup policy, and retention documentation.

# Security

## Implemented Controls

- Random public tokens instead of numeric IDs in download URLs.
- Private storage and controller-mediated downloads.
- CSRF protection for web requests.
- Rate limits for transfer creation, chunk upload, unlock, and downloads.
- Strong password hashing for optional transfer passwords.
- Security headers on web responses.
- Path traversal mitigation by never using original filenames as paths.
- Idempotent notification job.

## Operator Controls

- Configure upload size limits in Laravel, PHP, Nginx, and infrastructure.
- Configure `TRANSFER_ALLOWED_MIME_TYPES` if only specific file types are permitted.
- Enable and test ClamAV or another scanner before accepting untrusted production uploads.
- Keep Laravel, PHP, Node, and container base images patched.

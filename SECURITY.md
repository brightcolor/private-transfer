# Security Policy

## Supported Versions

`0.1.x` receives security fixes while the project is pre-1.0.

## Reporting A Vulnerability

Please report vulnerabilities privately to the repository owner. Include impact, reproduction steps, affected versions, and suggested mitigations when available.

## Security Baseline

- Transfer URLs use random non-enumerable tokens.
- Files are stored outside public web roots.
- Original filenames are metadata only and are not used as storage paths.
- Downloads are served through Laravel controllers.
- Upload starts and downloads are rate limited.
- Passwords are hashed with Laravel hashing.
- Optional virus scanning is prepared through configuration, but operators must wire and validate their scanner before relying on it.

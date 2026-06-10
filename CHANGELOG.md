# Changelog

All notable changes to this project are documented in this file.

The format is based on Keep a Changelog, and this project follows Semantic Versioning.

## [Unreleased]

### Added

- Upload completion page with copyable download link and transfer summary.
- Pause and resume control for active browser uploads.
- Per-transfer retention selection capped by the configured maximum retention.
- Polished download page metrics for file count, total size, and download count.

## [0.1.0] - 2026-06-10

### Added

- Initial Laravel application for private, self-hosted file transfers.
- Resumable chunk upload flow with persisted server-side offsets.
- Tokenized download pages with individual and ZIP downloads.
- Optional transfer password and download limit.
- Automatic queued email notification after transfer completion.
- Expired and stale transfer cleanup command.
- Storage statistics command.
- Docker, Nginx, PostgreSQL, Redis, worker, and scheduler setup.
- GitHub Actions CI and GHCR image workflow.
- Privacy, security, deployment, and architecture documentation.

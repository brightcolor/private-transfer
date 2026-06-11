# Changelog

All notable changes to this project are documented in this file.

The format is based on Keep a Changelog, and this project follows Semantic Versioning.

## [Unreleased]

### Added

- Upload completion page with copyable download link and transfer summary.
- Pause and resume control for active browser uploads.
- Per-transfer retention selection capped by the configured maximum retention.
- Polished download page metrics for file count, total size, and download count.
- Optional sender notification after the first successful download.
- Mail status indicators on the upload completion page.
- Quickinstall script for Linux Docker hosts with `/opt/private-transfer` data directories.
- Copy-paste curl command for installing the quickinstall script from the public GitHub repository.
- Automatic free random HTTP port selection in quickinstall when default port `8080` is already in use.
- GHCR publishing for separate app and Nginx images on `main`.

### Fixed

- Corrected Docker build targets so PHP services and the GHCR image build the app stage instead of the Nginx stage.
- Switched Docker persistence from named volumes to host bind mounts below `/opt/private-transfer`.
- Fixed the curl installer so it downloads the application source when launched outside a repository checkout.
- Added a cache-busting timestamp to the documented quickinstall curl command.
- Hardened quickinstall project detection so unrelated compose files do not skip source download.
- Changed quickinstall to pull prebuilt GHCR images instead of building locally.
- Fixed Docker vendor installation by making `artisan` available during Composer scripts.

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

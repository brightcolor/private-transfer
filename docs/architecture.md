# Architecture

Private Transfer is a small Laravel application with three main flows: transfer creation, resumable upload, and tokenized download.

## Components

- `TransferController` handles upload initialization, chunk ingestion, status, unlock, and downloads.
- `TransferService` owns transfer creation, chunk append validation, file completion, and transfer completion.
- `SendTransferNotification` sends mail once per completed transfer.
- `transfers:cleanup` removes expired transfers and stale chunks.
- `transfers:stats` reports basic storage statistics.

## Upload State

The server stores each file's expected byte offset. The browser sends chunks with the current offset. If the offset does not match, the server returns `409` and the client updates its local state from the server response.

## Storage

Chunks are temporary local files. Completed files are written to the configured filesystem disk. This keeps append behavior simple while allowing production files to live on S3-compatible storage.

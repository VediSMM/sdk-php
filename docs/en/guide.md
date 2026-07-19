# VediSMM PHP SDK guide

## Configuration and authentication

`Config` defaults to `https://vedismm.ru/api/v1`. A custom URL must use HTTPS; plain HTTP is accepted only for `localhost`, `127.0.0.1`, or `::1`. It cannot contain credentials, a query, or a fragment.

Pass either a token string or a zero-argument callback. The callback is evaluated for each authenticated request and is useful for short-lived tokens. The SDK does not write credentials to disk. Do not log `CallOptions` or application-owned token callbacks.

## Calling operations

Use named service methods for normal application code. Each one delegates to `Client::call()` and accepts `CallOptions`:

- `path` replaces `{parameters}` using RFC 3986 encoding;
- `query` supports scalar values and repeated arrays;
- `body` is encoded as JSON;
- `rawBody` accepts a string or readable stream;
- `multipart` accepts cURL multipart fields such as `new CURLFile(...)`;
- `idempotencyKey` sends `Idempotency-Key`;
- `ifMatch` sends a strong ETag through `If-Match`;
- `sink` streams the response to a caller-owned writable resource.

Unknown operations, missing/unused path parameters, invalid headers, unsafe base URLs, and malformed credentials fail before network I/O.

## Errors and retries

Unsuccessful API responses raise `ApiException`. Catch `RateLimitException` for `429` and inspect `retryAfterMs`; catch `PreconditionFailedException` for `412`. `TimeoutException`, `TransportException`, `RedirectException`, and `DecodeException` are separate categories. API errors preserve `status`, `errorCode`, `detail`, validation `errors`, and `requestId` after redaction.

The retry budget defaults to two additional attempts. `429`, `502`, `503`, and `504`, plus transient transport failures, are retried only for `GET`, `PUT`, `DELETE`, or calls carrying a stable idempotency key. `Retry-After` is capped at 30 seconds. Stream bodies are not replayed unless the caller explicitly marks a multipart source replayable and guarantees that it can be read again.

## Pagination

`Paginator::iterate()` accepts a callback that fetches one `ApiResponse`. It yields `data` items lazily and forwards only the opaque `meta.next_cursor` returned by the API. Repeated cursors are rejected as loops.

## Optimistic concurrency

Persist the `etag` property returned by resource reads and pass it as `CallOptions(ifMatch: $etag)` for versioned updates/deletes. On `PreconditionFailedException`, read the resource again and decide whether to apply the change to the newer version.

## Media streaming

For multipart upload use `CallOptions(multipart: ['file' => new CURLFile($path)])`. For a raw readable stream use `rawBody`. For large downloads open a writable binary handle and pass it through `sink`; the SDK never closes caller-owned streams.

## Publication jobs

Commands such as `publishPost` return `202`. `JobWaiter::wait()` polls through a caller-provided callback, has a finite timeout, supports a cancellation callback, returns `succeeded`/`partially_succeeded`, and throws `JobFailedException` for `failed`/`cancelled`. It leaves no background work behind.

## Webhooks

Pass the original request bytes, `X-VediSMM-Timestamp`, and `X-VediSMM-Signature` (`v1=<64 lowercase hex chars>`) to `WebhookVerifier::verify()`. Verification uses HMAC-SHA256 and `hash_equals`, rejects stale timestamps, and can atomically claim an event ID through `ReplayStoreInterface`. A production replay store should use an atomic database insert or distributed `SET NX` primitive.

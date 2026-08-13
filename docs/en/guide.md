# VediSMM PHP SDK guide

## Configuration and authentication

`Config` defaults to `https://vedismm.ru/api/v1`. A custom URL must use HTTPS; plain HTTP is accepted only for `localhost`, `127.0.0.1`, or `::1`. It cannot contain credentials, a query, or a fragment.

Pass either a token string or a zero-argument callback. The callback is evaluated for each authenticated request and is useful for short-lived tokens. The SDK does not write credentials to disk. Do not log `CallOptions` or application-owned token callbacks.

Authenticated calls send the same in-memory credential through both
`Authorization: Bearer` and VediSMM's `X-API-Token` proxy fallback. The API
gives `Authorization` priority. Both headers are SDK-managed, cannot be
overridden through `CallOptions`, and must never be logged.

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

`CallOptions::idempotent($key)` and `CallOptions::ifMatch($etag)` are concise
factories for the two managed conditional headers. Validation and header
serialization remain in `Client`.

## Tracking links

Create a short link by sending the original destination unchanged. The SDK
does not parse, normalize, shorten, append UTM parameters to, or otherwise
rewrite the URL:

```php
$created = $sdk->trackingLinks()->create(
    ['destination_url' => 'https://example.com/article?id=7#details'],
    CallOptions::idempotent('article-7-v1'),
);
```

Use `list()` for one cursor page and `iterate()` for lazy traversal. Read a
single resource with `get()`. A destination cannot be updated: create a new
link for a different URL. `disable()` and `archive()` are the only lifecycle
mutations and require the latest strong ETag:

```php
$link = $sdk->trackingLinks()->get(42);
if ($link->etag !== null) {
    $sdk->trackingLinks()->disable(42, CallOptions::ifMatch($link->etag));
}
```

## Tracking analytics

The six endpoints are `summary()`, `timeseries()`, `links()`, `posts()`,
`sources()`, and `geo()`. All take an array with required `from` and `to` ISO
dates; `link_id`, `post_id`, and `network` are optional. The three list
resources also accept `limit`/`cursor` and expose `iterateLinks()`,
`iteratePosts()`, and `iterateSources()` helpers that preserve the complete
initial filter while following opaque cursors.

```php
$geo = $sdk->trackingAnalytics()->geo([
    'from' => '2026-08-01',
    'to' => '2026-08-13',
    'network' => 'vk',
]);
```

Geo results contain country aggregates and privacy-minimized city GeoJSON;
they never expose raw IP addresses. During a dark rollout the API may return a
normal `ApiException` with status `503` and `errorCode === 'feature_disabled'`.

## Tracking settings for posts

Post creation accepts nested `options.tracking` with both required booleans.
Both default to `false`. `add_source` is meaningful only when
`shorten_links` is `true`; the server applies both settings when it creates the
delivery snapshot and never changes the saved author content.

```php
$sdk->posts->createPostDraft(
    [
        'title' => 'Example',
        'content' => 'Read https://example.com/article',
        'options' => [
            'tracking' => [
                'shorten_links' => true,
                'add_source' => true,
            ],
        ],
    ],
    CallOptions::idempotent('post-draft-1'),
);
```

The same typed request form is available on `updatePostDraft()`; pass its `id`
and current ETag through the second `CallOptions` argument. The original
single-`CallOptions` form remains supported for compatibility.

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

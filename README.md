# VediSMM PHP SDK

Official native PHP client for all 94 operations of the VediSMM user API v1. The package contains no administrative API.

[Русская версия](README.ru.md) · [API documentation](https://vedismm.ru/docs/api) · [Security policy](SECURITY.md)

## Requirements

- PHP 8.1–8.4
- Composer 2
- `ext-curl` and `ext-json`

## Install from the immutable GitHub release

Until the package is published on Packagist, register the official repository once and require the tagged version:

```bash
composer config repositories.vedismm-sdk vcs https://github.com/VediSMM/sdk-php
composer require vedismm/sdk:0.2.0
```

## Quick start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use VediSMM\Client;
use VediSMM\Config;
use VediSMM\VediSMM;

$sdk = new VediSMM(new Client(new Config(
    accessToken: getenv('VEDISMM_TOKEN') ?: null,
)));

$me = $sdk->profile->getMe();
echo $me->data['data']['email'] . PHP_EOL;
```

The facade exposes `system`, `auth`, `profile`, `sessions`, `audit`, `personalTokens`, `preferences`, `networks`, `connections`, `accounts`, `groups`, `media`, `posts`, `jobs`, `calendar`, `analytics`, `trackingLinks`, `trackingAnalytics`, and `webhooks` services. Generic services accept an optional immutable `CallOptions`; focused tracking services add typed request arrays and responses.

## Tracking links and click analytics

```php
use VediSMM\Value\CallOptions;

$link = $sdk->trackingLinks()->create(
    ['destination_url' => 'https://example.com/article'],
    CallOptions::idempotent('article-v1'),
);

$summary = $sdk->trackingAnalytics()->summary([
    'from' => '2026-08-01',
    'to' => '2026-08-13',
]);
```

Destinations are immutable and there is no update method. The SDK sends URLs
unchanged; shortening, source attribution, and privacy-safe aggregation are
server responsibilities. Disable/archive calls use
`CallOptions::ifMatch($etag)`, and list helpers follow server cursors through
the existing `Paginator`.

## Safety defaults

- Bearer credentials are never persisted or included in string representations.
- Authenticated redirects are rejected instead of forwarding credentials to another origin.
- Retries are finite and limited to safe methods or requests with a stable idempotency key.
- Problem Details, rate limits, precondition failures, timeouts, transport failures, and decoding failures use distinct exceptions.
- Downloads can stream into a caller-owned sink; uploads accept streams or cURL multipart fields.
- Webhook verification signs the original bytes and supports an atomic replay store.

See the [English guide](docs/en/guide.md) and [API reference](docs/en/api-reference.md) for tracking settings, click analytics, pagination, ETags, media, jobs, and webhooks.

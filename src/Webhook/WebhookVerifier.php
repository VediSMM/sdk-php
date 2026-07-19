<?php

declare(strict_types=1);

namespace VediSMM\Webhook;

use VediSMM\Exception\ConfigurationException;

final class WebhookVerifier
{
    public static function verify(
        string $secret,
        string $timestamp,
        string $signature,
        string $rawBody,
        int $toleranceSeconds = 300,
        ?int $now = null,
        ?string $eventId = null,
        ?ReplayStoreInterface $replayStore = null,
    ): bool {
        if ($secret === '') {
            throw new ConfigurationException('webhook secret must not be empty');
        }
        if ($toleranceSeconds < 0) {
            throw new ConfigurationException('webhook toleranceSeconds must be a non-negative integer');
        }
        if (preg_match('/^\d{10,}$/D', $timestamp) !== 1
            || preg_match('/^v1=([0-9a-f]{64})$/D', $signature, $matches) !== 1) {
            return false;
        }
        $timestampValue = filter_var($timestamp, FILTER_VALIDATE_INT);
        if (!\is_int($timestampValue)) {
            return false;
        }
        $now ??= time();
        if (abs($now - $timestampValue) > $toleranceSeconds) {
            return false;
        }
        $actual = hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);
        if (!hash_equals($actual, $matches[1])) {
            return false;
        }
        if ($eventId !== null && $replayStore !== null) {
            return $replayStore->claim($eventId, $now + $toleranceSeconds);
        }

        return true;
    }
}

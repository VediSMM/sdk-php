<?php

declare(strict_types=1);

namespace VediSMM\Transport;

final class TransportRequest
{
    /**
     * @param array<string, string> $headers
     * @param mixed $body String, readable stream resource, or cURL multipart fields.
     * @param mixed $sink Writable stream resource.
     */
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public readonly array $headers,
        public readonly mixed $body,
        public readonly int $timeoutMs,
        public readonly int $maxResponseBytes,
        public readonly mixed $sink = null,
    ) {}
}

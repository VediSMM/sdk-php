<?php

declare(strict_types=1);

namespace VediSMM\Transport;

final class TransportResponse
{
    /** @param array<string, string> $headers */
    public function __construct(
        public readonly int $status,
        public readonly array $headers,
        public readonly string $body,
    ) {}
}

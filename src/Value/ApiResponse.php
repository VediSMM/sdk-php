<?php

declare(strict_types=1);

namespace VediSMM\Value;

/** @template T */
final class ApiResponse
{
    /**
     * @param array<string, string> $headers
     * @param T $data
     */
    public function __construct(
        public readonly int $status,
        public readonly array $headers,
        public readonly mixed $data,
        public readonly ?string $requestId = null,
        public readonly ?string $etag = null,
    ) {}
}

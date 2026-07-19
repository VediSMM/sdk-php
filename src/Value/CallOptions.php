<?php

declare(strict_types=1);

namespace VediSMM\Value;

final class CallOptions
{
    /**
     * @param array<string, int|string> $path
     * @param array<string, bool|float|int|string|null|list<bool|float|int|string|null>> $query
     * @param array<string, string> $headers
     * @param mixed $body JSON-serializable body.
     * @param mixed $rawBody String or readable stream resource.
     * @param null|array<string, mixed> $multipart cURL multipart fields.
     * @param mixed $sink Writable stream resource for a streaming response.
     */
    public function __construct(
        public readonly array $path = [],
        public readonly array $query = [],
        public readonly mixed $body = null,
        public readonly mixed $rawBody = null,
        public readonly ?array $multipart = null,
        public readonly array $headers = [],
        public readonly ?string $idempotencyKey = null,
        public readonly ?string $ifMatch = null,
        public readonly ?int $timeoutMs = null,
        public readonly mixed $sink = null,
        public readonly bool $replayable = false,
    ) {}
}

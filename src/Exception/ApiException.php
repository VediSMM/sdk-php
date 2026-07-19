<?php

declare(strict_types=1);

namespace VediSMM\Exception;

class ApiException extends VediSMMException
{
    /** @param mixed $errors */
    public function __construct(
        public readonly int $status,
        public readonly string $errorCode,
        public readonly string $detail,
        public readonly mixed $errors = null,
        ?string $requestId = null,
        public readonly ?int $retryAfterMs = null,
    ) {
        parent::__construct(
            \sprintf('VediSMM API error %d (%s): %s', $status, $errorCode, $detail),
            $requestId,
        );
    }
}

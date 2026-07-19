<?php

declare(strict_types=1);

namespace VediSMM\Exception;

use RuntimeException;

class VediSMMException extends RuntimeException
{
    public function __construct(string $message, public readonly ?string $requestId = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

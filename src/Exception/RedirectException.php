<?php

declare(strict_types=1);

namespace VediSMM\Exception;

final class RedirectException extends VediSMMException
{
    public function __construct(public readonly int $status, ?string $requestId = null)
    {
        parent::__construct(\sprintf('VediSMM API redirect rejected (%d)', $status), $requestId);
    }
}

<?php

declare(strict_types=1);

namespace VediSMM\Exception;

final class JobFailedException extends VediSMMException
{
    /** @param array<mixed> $job */
    public function __construct(public readonly array $job)
    {
        $rawId = $job['id'] ?? null;
        $rawStatus = $job['status'] ?? null;
        $id = \is_string($rawId) || \is_int($rawId) ? (string) $rawId : 'unknown';
        $status = \is_string($rawStatus) ? $rawStatus : 'unknown';
        parent::__construct(\sprintf(
            'VediSMM publication job %s ended with status %s',
            $id,
            $status,
        ));
    }
}

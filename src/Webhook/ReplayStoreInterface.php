<?php

declare(strict_types=1);

namespace VediSMM\Webhook;

interface ReplayStoreInterface
{
    public function claim(string $eventId, int $expiresAt): bool;
}

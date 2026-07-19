<?php

declare(strict_types=1);

namespace VediSMM\Webhook;

final class AtomicMemoryReplayStore implements ReplayStoreInterface
{
    /** @var array<string, int> */
    private array $claims = [];

    public function claim(string $eventId, int $expiresAt): bool
    {
        if (isset($this->claims[$eventId])) {
            return false;
        }
        $this->claims[$eventId] = $expiresAt;

        return true;
    }
}

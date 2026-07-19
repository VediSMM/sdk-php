<?php

declare(strict_types=1);

namespace VediSMM\Tests\Support;

use RuntimeException;
use Throwable;
use VediSMM\Transport\TransportInterface;
use VediSMM\Transport\TransportRequest;
use VediSMM\Transport\TransportResponse;

final class FakeTransport implements TransportInterface
{
    /** @var list<TransportRequest> */
    public array $requests = [];

    /** @var list<TransportResponse|Throwable|callable(TransportRequest): TransportResponse> */
    private array $queue;

    /** @param list<TransportResponse|Throwable|callable(TransportRequest): TransportResponse> $queue */
    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    public function send(TransportRequest $request): TransportResponse
    {
        $this->requests[] = $request;
        $next = array_shift($this->queue);
        if ($next === null) {
            throw new RuntimeException('Fake transport queue exhausted');
        }
        if ($next instanceof Throwable) {
            throw $next;
        }
        if (\is_callable($next)) {
            return $next($request);
        }

        return $next;
    }
}

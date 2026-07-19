<?php

declare(strict_types=1);

namespace VediSMM\Helper;

use Closure;
use VediSMM\Exception\CancelledException;
use VediSMM\Exception\ConfigurationException;
use VediSMM\Exception\JobFailedException;
use VediSMM\Exception\TimeoutException;
use VediSMM\Exception\VediSMMException;

final class JobWaiter
{
    /**
     * @param callable(): array<mixed> $fetchJob
     * @param null|callable(int): void $sleeper Receives milliseconds.
     * @param null|callable(): int $now Returns milliseconds.
     * @param null|callable(): bool $cancelled
     * @return array<mixed>
     */
    public static function wait(
        callable $fetchJob,
        int $timeoutMs = 120000,
        int $pollIntervalMs = 1000,
        ?callable $sleeper = null,
        ?callable $now = null,
        ?callable $cancelled = null,
    ): array {
        if ($timeoutMs <= 0 || $pollIntervalMs <= 0) {
            throw new ConfigurationException('timeoutMs and pollIntervalMs must be positive integers');
        }
        $sleep = $sleeper === null
            ? static function (int $milliseconds): void {
                usleep($milliseconds * 1000);
            }
        : Closure::fromCallable($sleeper);
        $clock = $now === null
            ? static fn(): int => (int) round(microtime(true) * 1000)
            : Closure::fromCallable($now);
        $isCancelled = $cancelled === null
            ? static fn(): bool => false
            : Closure::fromCallable($cancelled);
        $deadline = $clock() + $timeoutMs;

        while (true) {
            if ($isCancelled()) {
                throw new CancelledException('VediSMM job wait was cancelled');
            }
            $job = $fetchJob();
            $status = $job['status'] ?? null;
            if (!\is_string($status) || !\is_string($job['id'] ?? null)) {
                throw new VediSMMException('VediSMM API returned an invalid publication job');
            }
            if (\in_array($status, ['succeeded', 'partially_succeeded'], true)) {
                return $job;
            }
            if (\in_array($status, ['failed', 'cancelled'], true)) {
                throw new JobFailedException($job);
            }
            $remaining = $deadline - $clock();
            if ($remaining <= 0) {
                throw new TimeoutException(\sprintf('VediSMM publication job %s did not finish in time', $job['id']));
            }
            $sleep(min($pollIntervalMs, $remaining));
        }
    }
}

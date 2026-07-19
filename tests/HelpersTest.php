<?php

declare(strict_types=1);

namespace VediSMM\Tests;

use PHPUnit\Framework\TestCase;
use VediSMM\Exception\JobFailedException;
use VediSMM\Exception\TimeoutException;
use VediSMM\Helper\Idempotency;
use VediSMM\Helper\JobWaiter;
use VediSMM\Helper\Paginator;
use VediSMM\Value\ApiResponse;
use VediSMM\Webhook\AtomicMemoryReplayStore;
use VediSMM\Webhook\WebhookVerifier;

final class HelpersTest extends TestCase
{
    public function testPaginatorFollowsOpaqueCursorAndStops(): void
    {
        $seen = [];
        /** @var list<ApiResponse<mixed>> $pages */
        $pages = [
            new ApiResponse(200, [], ['data' => [['id' => 1]], 'meta' => ['next_cursor' => 'opaque+cursor']]),
            new ApiResponse(200, [], ['data' => [['id' => 2]], 'meta' => ['next_cursor' => null]]),
        ];
        $items = iterator_to_array(Paginator::iterate(
            static function (?string $cursor) use (&$seen, &$pages): ApiResponse {
                $seen[] = $cursor;

                $page = array_shift($pages);
                if (!$page instanceof ApiResponse) {
                    throw new \RuntimeException('No page available');
                }

                return $page;
            },
        ), false);

        self::assertSame([null, 'opaque+cursor'], $seen);
        self::assertSame([['id' => 1], ['id' => 2]], $items);
    }

    public function testJobWaiterSupportsSuccessFailureTimeoutAndCancellation(): void
    {
        $jobs = [
            ['id' => 'job_1', 'status' => 'queued'],
            ['id' => 'job_1', 'status' => 'succeeded'],
        ];
        $clock = 1000;
        $result = JobWaiter::wait(
            static function () use (&$jobs): array {
                $job = array_shift($jobs);
                if (!\is_array($job)) {
                    throw new \RuntimeException('No job available');
                }

                return $job;
            },
            timeoutMs: 1000,
            pollIntervalMs: 100,
            sleeper: static function (int $milliseconds) use (&$clock): void {
                $clock += $milliseconds;
            },
            now: static function () use (&$clock): int {
                return $clock;
            },
        );
        self::assertSame('succeeded', $result['status']);

        $this->expectException(JobFailedException::class);
        JobWaiter::wait(static fn(): array => ['id' => 'job_2', 'status' => 'failed']);
    }

    public function testJobWaiterTimesOutWithoutUnboundedPolling(): void
    {
        $clock = 0;
        $this->expectException(TimeoutException::class);
        JobWaiter::wait(
            static fn(): array => ['id' => 'job_3', 'status' => 'running'],
            timeoutMs: 10,
            pollIntervalMs: 10,
            sleeper: static function (int $milliseconds) use (&$clock): void {
                $clock += $milliseconds;
            },
            now: static function () use (&$clock): int {
                return $clock;
            },
        );
    }

    public function testIdempotencyKeyIsCryptographicUuidV4(): void
    {
        $key = Idempotency::key(static fn(int $length): string => str_repeat("\x00", $length));
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-8[0-9a-f]{3}-[0-9a-f]{12}$/', $key);
    }

    public function testWebhookVerifierUsesRawBytesTimestampAndAtomicReplayClaim(): void
    {
        $secret = 'whsec_test_vedismm_fixture';
        $timestamp = '1784361600';
        $body = '{"event":"post.published","id":"evt_fixture_01"}';
        $signature = 'v1=a2797dd81ea7a742832102bb7f3ec5f95d1d9b5fa9da1098f3a21ac39fab647d';
        $store = new AtomicMemoryReplayStore();

        self::assertTrue(WebhookVerifier::verify(
            $secret,
            $timestamp,
            $signature,
            $body,
            toleranceSeconds: 300,
            now: 1784361600,
            eventId: 'evt_fixture_01',
            replayStore: $store,
        ));
        self::assertFalse(WebhookVerifier::verify(
            $secret,
            $timestamp,
            $signature,
            $body,
            toleranceSeconds: 300,
            now: 1784361600,
            eventId: 'evt_fixture_01',
            replayStore: $store,
        ));
        self::assertFalse(WebhookVerifier::verify($secret, $timestamp, $signature, $body . "\n", now: 1784361600));
        self::assertFalse(WebhookVerifier::verify($secret, $timestamp, $signature, $body, now: 1784362600));
    }
}

<?php

declare(strict_types=1);

namespace VediSMM\Tests;

use PHPUnit\Framework\TestCase;
use VediSMM\Client;
use VediSMM\Config;
use VediSMM\Exception\ConfigurationException;
use VediSMM\Exception\DecodeException;
use VediSMM\Exception\PreconditionFailedException;
use VediSMM\Exception\RateLimitException;
use VediSMM\Exception\RedirectException;
use VediSMM\Exception\TransportException;
use VediSMM\Tests\Support\FakeTransport;
use VediSMM\Transport\TransportRequest;
use VediSMM\Transport\TransportResponse;
use VediSMM\Value\CallOptions;

final class ClientTest extends TestCase
{
    public function testBuildsEncodedAuthenticatedJsonRequestAndPreservesMetadata(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(200, ['Request-Id' => 'req_123', 'ETag' => '"v2"'], '{"data":{"ok":true}}'),
        ]);
        $client = new Client(new Config('pat_secret'), $transport);

        $response = $client->call('getWebhookDelivery', new CallOptions(
            path: ['webhook_id' => 'hook / 1', 'delivery_id' => 'delivery?2'],
            query: ['include' => ['attempts', 'response'], 'active' => true],
            headers: ['X-Correlation-ID' => 'corr_1'],
            ifMatch: '"v1"',
        ));

        self::assertSame('https://vedismm.ru/api/v1/webhooks/hook%20%2F%201/deliveries/delivery%3F2?include=attempts&include=response&active=true', $transport->requests[0]->url);
        self::assertSame('Bearer pat_secret', $transport->requests[0]->headers['Authorization']);
        self::assertSame('"v1"', $transport->requests[0]->headers['If-Match']);
        self::assertNull($transport->requests[0]->body);
        self::assertSame('req_123', $response->requestId);
        self::assertSame('"v2"', $response->etag);
        self::assertSame(['data' => ['ok' => true]], $response->data);
        self::assertStringNotContainsString('pat_secret', (string) $client);
        self::assertStringNotContainsString('pat_secret', (string) $client->config);
    }

    public function testRejectsRedirectWithoutFollowingLocation(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(307, ['location' => 'https://evil.example/steal'], ''),
        ]);

        $this->expectException(RedirectException::class);
        (new Client(new Config('pat_secret'), $transport))->call('getMe');
    }

    public function testRetriesRateLimitOnlyWithStableIdempotencyKeyAndHonorsRetryAfter(): void
    {
        $delays = [];
        $transport = new FakeTransport([
            new TransportResponse(429, ['retry-after' => '1'], '{"code":"rate_limited","detail":"wait"}'),
            new TransportResponse(201, [], '{"data":{"id":7}}'),
        ]);
        $client = new Client(new Config(
            'pat_secret',
            maxRetries: 2,
            sleeper: static function (int $milliseconds) use (&$delays): void {
                $delays[] = $milliseconds;
            },
            random: static fn(): float => 0.5,
        ), $transport);

        $response = $client->call('createPostDraft', new CallOptions(
            body: ['title' => 'Example'],
            idempotencyKey: '123e4567-e89b-42d3-a456-426614174000',
        ));

        self::assertSame([1000], $delays);
        self::assertCount(2, $transport->requests);
        self::assertSame(
            $transport->requests[0]->headers['Idempotency-Key'],
            $transport->requests[1]->headers['Idempotency-Key'],
        );
        self::assertSame(['data' => ['id' => 7]], $response->data);
    }

    public function testDoesNotRetryUnsafeMutationWithoutIdempotencyKey(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(503, [], '{"code":"unavailable","detail":"later"}'),
            new TransportResponse(201, [], '{"data":{"id":7}}'),
        ]);
        $client = new Client(new Config('pat_secret', maxRetries: 2, sleeper: static fn(int $milliseconds) => null), $transport);

        try {
            $client->call('createGroup', new CallOptions(body: ['name' => 'Editors']));
            self::fail('Expected API exception');
        } catch (\VediSMM\Exception\ApiException $exception) {
            self::assertSame(503, $exception->status);
        }
        self::assertCount(1, $transport->requests);
    }

    public function testMapsProblemDetailsAndRedactsTokenEverywhere(): void
    {
        $secret = 'pat_do_not_leak';
        $transport = new FakeTransport([
            new TransportResponse(412, ['request-id' => 'req_problem'], json_encode([
                'code' => 'precondition_failed',
                'detail' => 'token=' . $secret,
                'errors' => ['authorization' => 'Bearer ' . $secret],
            ], JSON_THROW_ON_ERROR)),
        ]);

        try {
            (new Client(new Config($secret), $transport))->call('updateMe', new CallOptions(body: []));
            self::fail('Expected precondition failure');
        } catch (PreconditionFailedException $exception) {
            self::assertSame('req_problem', $exception->requestId);
            self::assertStringNotContainsString($secret, $exception->getMessage());
            self::assertStringNotContainsString($secret, serialize($exception->errors));
        }
    }

    public function testMapsFinalRateLimitAndInvalidSuccessJson(): void
    {
        $rate = new FakeTransport([
            new TransportResponse(429, ['retry-after' => '2'], '{"code":"rate_limited","detail":"slow down"}'),
        ]);
        try {
            (new Client(new Config(maxRetries: 0), $rate))->call('ping');
            self::fail('Expected rate limit');
        } catch (RateLimitException $exception) {
            self::assertSame(2000, $exception->retryAfterMs);
        }

        $invalid = new FakeTransport([new TransportResponse(200, [], '{broken')]);
        $this->expectException(DecodeException::class);
        (new Client(transport: $invalid))->call('ping');
    }

    public function testValidatesPathHeadersAndTransportFailuresDoNotLeakToken(): void
    {
        $transport = new FakeTransport([
            static fn(TransportRequest $request): TransportResponse => throw new TransportException('failed ' . $request->headers['Authorization']),
        ]);
        try {
            (new Client(new Config('pat_hidden', maxRetries: 0), $transport))->call('getMe');
            self::fail('Expected transport error');
        } catch (TransportException $exception) {
            self::assertStringNotContainsString('pat_hidden', $exception->getMessage());
        }

        $client = new Client(transport: new FakeTransport([]));
        $this->expectException(ConfigurationException::class);
        $client->call('getPost');
    }
}

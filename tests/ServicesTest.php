<?php

declare(strict_types=1);

namespace VediSMM\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VediSMM\Client;
use VediSMM\Config;
use VediSMM\Exception\ApiException;
use VediSMM\Tests\Support\FakeTransport;
use VediSMM\Transport\TransportRequest;
use VediSMM\Transport\TransportResponse;
use VediSMM\Value\CallOptions;
use VediSMM\VediSMM as SDK;

final class ServicesTest extends TestCase
{
    public function testTrackingLinkCreateSendsTheDestinationAndIdempotencyKeyUnchanged(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(201, ['ETag' => '"1"'], '{"data":{"id":42}}'),
        ]);
        $sdk = new SDK(new Client(new Config('pat_tracking'), $transport));

        $response = $sdk->trackingLinks()->create(
            ['destination_url' => 'https://example.com/articles?id=7#details'],
            CallOptions::idempotent('create-1'),
        );

        self::assertSame(['data' => ['id' => 42]], $response->data);
        self::assertSame('"1"', $response->etag);
        self::assertCount(1, $transport->requests);
        self::assertSame('post', $transport->requests[0]->method);
        self::assertSame('/api/v1/tracking-links', parse_url($transport->requests[0]->url, PHP_URL_PATH));
        self::assertSame('create-1', $transport->requests[0]->headers['Idempotency-Key'] ?? null);
        self::assertSame(
            '{"destination_url":"https://example.com/articles?id=7#details"}',
            $transport->requests[0]->body,
        );
    }

    public function testTrackingLinksExposeReadAndImmutableLifecycleOnly(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(200, [], '{"data":[],"meta":{"next_cursor":null,"has_more":false,"limit":25}}'),
            new TransportResponse(200, ['ETag' => '"1"'], '{"data":{"id":42}}'),
            new TransportResponse(200, ['ETag' => '"2"'], '{"data":{"id":42}}'),
            new TransportResponse(200, ['ETag' => '"3"'], '{"data":{"id":42}}'),
        ]);
        $sdk = new SDK(new Client(new Config('pat_tracking'), $transport));

        $sdk->trackingLinks()->list(['limit' => 25]);
        $sdk->trackingLinks()->get(42);
        $sdk->trackingLinks()->disable(42, CallOptions::ifMatch('"1"'));
        $sdk->trackingLinks()->archive(42, CallOptions::ifMatch('"2"'));

        self::assertNotContains('update', get_class_methods($sdk->trackingLinks()));
        self::assertSame(
            [
                'get /api/v1/tracking-links?limit=25',
                'get /api/v1/tracking-links/42',
                'post /api/v1/tracking-links/42/disable',
                'post /api/v1/tracking-links/42/archive',
            ],
            array_map(
                static fn(TransportRequest $request): string => $request->method . ' ' . substr($request->url, \strlen('https://vedismm.ru')),
                $transport->requests,
            ),
        );
        self::assertSame('"1"', $transport->requests[2]->headers['If-Match'] ?? null);
        self::assertSame('"2"', $transport->requests[3]->headers['If-Match'] ?? null);
        self::assertSame([null, null], [$transport->requests[2]->body, $transport->requests[3]->body]);
    }

    public function testTrackingLinkIteratorPreservesTheInitialQueryAndFollowsTheServerCursor(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(200, [], '{"data":[{"id":1}],"meta":{"next_cursor":"opaque-2","has_more":true,"limit":1}}'),
            new TransportResponse(200, [], '{"data":[{"id":2}],"meta":{"next_cursor":null,"has_more":false,"limit":1}}'),
        ]);
        $sdk = new SDK(new Client(new Config('pat_tracking'), $transport));

        $items = iterator_to_array($sdk->trackingLinks()->iterate(['limit' => 1]));

        self::assertSame([['id' => 1], ['id' => 2]], $items);
        self::assertSame(
            [
                'https://vedismm.ru/api/v1/tracking-links?limit=1',
                'https://vedismm.ru/api/v1/tracking-links?limit=1&cursor=opaque-2',
            ],
            array_map(static fn(TransportRequest $request): string => $request->url, $transport->requests),
        );
    }

    public function testTrackingAnalyticsExposeAllSixEndpointsWithExactFilters(): void
    {
        $transport = new FakeTransport(array_fill(
            0,
            6,
            new TransportResponse(200, [], '{"data":[],"meta":{"filter":{"from":"2026-08-01","to":"2026-08-13","link_id":null,"post_id":null,"network":"vk"}}}'),
        ));
        $sdk = new SDK(new Client(new Config('pat_tracking'), $transport));
        $filter = ['from' => '2026-08-01', 'to' => '2026-08-13', 'network' => 'vk'];

        $sdk->trackingAnalytics()->summary($filter);
        $sdk->trackingAnalytics()->timeseries($filter);
        $sdk->trackingAnalytics()->links($filter);
        $sdk->trackingAnalytics()->posts($filter);
        $sdk->trackingAnalytics()->sources($filter);
        $sdk->trackingAnalytics()->geo($filter);

        self::assertSame(
            [
                '/api/v1/tracking-analytics/summary',
                '/api/v1/tracking-analytics/timeseries',
                '/api/v1/tracking-analytics/links',
                '/api/v1/tracking-analytics/posts',
                '/api/v1/tracking-analytics/sources',
                '/api/v1/tracking-analytics/geo',
            ],
            array_map(static fn(TransportRequest $request): string => (string) parse_url($request->url, PHP_URL_PATH), $transport->requests),
        );
        foreach ($transport->requests as $request) {
            self::assertSame('from=2026-08-01&to=2026-08-13&network=vk', parse_url($request->url, PHP_URL_QUERY));
        }
    }

    /** @return iterable<string, array{string, string}> */
    public static function trackingAnalyticsListIterators(): iterable
    {
        yield 'links' => ['iterateLinks', 'link_id'];
        yield 'posts' => ['iteratePosts', 'post_id'];
        yield 'sources' => ['iterateSources', 'value'];
    }

    #[DataProvider('trackingAnalyticsListIterators')]
    public function testTrackingAnalyticsListIteratorsPreserveFiltersAndUseOpaqueCursors(
        string $iterator,
        string $identity,
    ): void {
        $transport = new FakeTransport([
            new TransportResponse(200, [], json_encode([
                'data' => [[$identity => 'first']],
                'meta' => ['next_cursor' => 'page-2', 'has_more' => true, 'limit' => 1, 'filter' => []],
            ], JSON_THROW_ON_ERROR)),
            new TransportResponse(200, [], json_encode([
                'data' => [[$identity => 'second']],
                'meta' => ['next_cursor' => null, 'has_more' => false, 'limit' => 1, 'filter' => []],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $sdk = new SDK(new Client(new Config('pat_tracking'), $transport));
        $query = [
            'from' => '2026-08-01',
            'to' => '2026-08-13',
            'limit' => 1,
        ];

        $items = match ($iterator) {
            'iterateLinks' => iterator_to_array($sdk->trackingAnalytics()->iterateLinks($query)),
            'iteratePosts' => iterator_to_array($sdk->trackingAnalytics()->iteratePosts($query)),
            'iterateSources' => iterator_to_array($sdk->trackingAnalytics()->iterateSources($query)),
            default => self::fail('Unknown analytics iterator'),
        };

        self::assertSame([[$identity => 'first'], [$identity => 'second']], $items);
        self::assertSame(
            [
                'from=2026-08-01&to=2026-08-13&limit=1',
                'from=2026-08-01&to=2026-08-13&limit=1&cursor=page-2',
            ],
            array_map(static fn(TransportRequest $request): string => (string) parse_url($request->url, PHP_URL_QUERY), $transport->requests),
        );
    }

    public function testTrackingServicesPropagateFeatureDisabledProblemDetails(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(503, ['Request-Id' => 'req_disabled'], '{"code":"feature_disabled","detail":"Tracking is not enabled"}'),
        ]);
        $sdk = new SDK(new Client(new Config('pat_tracking', maxRetries: 0), $transport));

        try {
            $sdk->trackingAnalytics()->geo(['from' => '2026-08-01', 'to' => '2026-08-13']);
            self::fail('Expected feature-disabled API exception');
        } catch (ApiException $exception) {
            self::assertSame(503, $exception->status);
            self::assertSame('feature_disabled', $exception->errorCode);
            self::assertSame('req_disabled', $exception->requestId);
        }
        self::assertCount(1, $transport->requests);
    }

    public function testPostCreateAndUpdateAcceptTypedTrackingRequestsWithoutInjectingDefaults(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(201, [], '{"data":{"id":42}}'),
            new TransportResponse(200, [], '{"data":{"id":42}}'),
            new TransportResponse(201, [], '{"data":{"id":43}}'),
        ]);
        $sdk = new SDK(new Client(new Config('pat_tracking'), $transport));
        $tracking = [
            'title' => 'Tracked',
            'options' => [
                'tracking' => [
                    'shorten_links' => false,
                    'add_source' => false,
                ],
            ],
        ];

        $sdk->posts->createPostDraft($tracking, CallOptions::idempotent('post-create-1'));
        $sdk->posts->updatePostDraft($tracking, new CallOptions(path: ['id' => 42], ifMatch: '"1"'));
        $sdk->posts->createPostDraft(['title' => 'No tracking'], CallOptions::idempotent('post-create-2'));

        self::assertSame(json_encode($tracking, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES), $transport->requests[0]->body);
        self::assertSame(json_encode($tracking, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES), $transport->requests[1]->body);
        self::assertSame('{"title":"No tracking"}', $transport->requests[2]->body);
        self::assertSame('post-create-1', $transport->requests[0]->headers['Idempotency-Key'] ?? null);
        self::assertSame('"1"', $transport->requests[1]->headers['If-Match'] ?? null);
        self::assertSame('/api/v1/posts/42', parse_url($transport->requests[1]->url, PHP_URL_PATH));
    }

    public function testEveryCanonicalOperationHasExactlyOneNamedServiceMethod(): void
    {
        $raw = file_get_contents(__DIR__ . '/../contract/sdk-operations.json');
        self::assertIsString($raw);
        $manifest = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($manifest) || !\is_array($manifest['operations'] ?? null)) {
            self::fail('Manifest operations must be an array');
        }
        $expected = [];
        foreach ($manifest['operations'] as $operation) {
            if (!\is_array($operation) || !\is_string($operation['operation_id'] ?? null)) {
                self::fail('Every manifest operation must have an operation_id');
            }
            $expected[] = $operation['operation_id'];
        }
        sort($expected);

        $ownership = SDK::operationOwnership();
        $actual = [];
        foreach ($ownership as $service => $operations) {
            self::assertNotSame([], $operations, $service);
            foreach ($operations as $operation) {
                self::assertArrayNotHasKey($operation, $actual, 'duplicate operation ownership');
                $actual[$operation] = $service;
            }
        }
        $actualIds = array_keys($actual);
        sort($actualIds);

        self::assertCount(94, $actualIds);
        self::assertSame($expected, $actualIds);
    }

    public function testNamedServiceMethodDelegatesToClient(): void
    {
        $transport = new FakeTransport([
            new TransportResponse(200, [], '{"data":{"id":42}}'),
        ]);
        $sdk = new SDK(new Client(new Config('pat_service'), $transport));
        $response = $sdk->posts->getPost(new \VediSMM\Value\CallOptions(path: ['id' => 42]));

        self::assertSame(['data' => ['id' => 42]], $response->data);
        self::assertSame('/api/v1/posts/42', parse_url($transport->requests[0]->url, PHP_URL_PATH));
    }
}

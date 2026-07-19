<?php

declare(strict_types=1);

namespace VediSMM\Tests;

use PHPUnit\Framework\TestCase;
use VediSMM\Client;
use VediSMM\Config;
use VediSMM\Tests\Support\FakeTransport;
use VediSMM\Transport\TransportResponse;
use VediSMM\VediSMM as SDK;

final class ServicesTest extends TestCase
{
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

        self::assertCount(83, $actualIds);
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

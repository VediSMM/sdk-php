<?php

declare(strict_types=1);

namespace VediSMM\Tests;

use PHPUnit\Framework\TestCase;
use VediSMM\Operation;
use VediSMM\OperationCatalog;

final class ContractTest extends TestCase
{
    public function testCatalogExactlyMatchesCanonicalUserOperations(): void
    {
        $raw = file_get_contents(__DIR__ . '/../contract/sdk-operations.json');
        self::assertIsString($raw);
        $manifest = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($manifest)) {
            self::fail('Manifest must decode to an object');
        }
        self::assertSame('1.1.0', $manifest['contract_version'] ?? null);
        self::assertSame('08e7b140c93a1ef16aa54de509799388e8b01da36b93e47e9733a36e57e40f8a', $manifest['contract_sha256'] ?? null);
        self::assertSame(94, $manifest['operation_count'] ?? null);
        self::assertSame(
            'ef9aa367244e0ac40038436ee241a677eb9c6f009aafa7694d98972667fd23b7',
            hash('sha256', $raw),
        );
        $operations = $manifest['operations'] ?? null;
        if (!\is_array($operations)) {
            self::fail('Manifest operations must be an array');
        }
        $expected = [];
        foreach ($operations as $operation) {
            if (!\is_array($operation) || !\is_string($operation['operation_id'] ?? null)) {
                self::fail('Every manifest operation must have an operation_id');
            }
            $expected[] = $operation['operation_id'];
        }
        $actual = array_keys(OperationCatalog::all());
        sort($expected);
        sort($actual);

        self::assertCount(94, $actual);
        self::assertSame($expected, $actual);
        self::assertSame([], array_filter(
            OperationCatalog::all(),
            static fn(array $operation): bool => str_starts_with($operation['path'], '/admin'),
        ));
    }

    public function testCatalogContainsTheExactTrackingInventoryAndGenericCapabilities(): void
    {
        $expected = [
            'archiveTrackingLink',
            'createTrackingLink',
            'disableTrackingLink',
            'getTrackingAnalyticsGeo',
            'getTrackingAnalyticsSummary',
            'getTrackingAnalyticsTimeseries',
            'getTrackingLink',
            'listTrackingAnalyticsLinks',
            'listTrackingAnalyticsPosts',
            'listTrackingAnalyticsSources',
            'listTrackingLinks',
        ];
        $actual = [];
        foreach (OperationCatalog::all() as $id => $operation) {
            if (str_starts_with($operation['tag'], 'Tracking ')) {
                $actual[] = $id;
            }
        }

        self::assertSame($expected, $actual);
        self::assertTrue(OperationCatalog::get('createTrackingLink')->supports('etag_response'));
        self::assertTrue(OperationCatalog::get('createTrackingLink')->supports('service_unavailable'));

        $future = Operation::fromArray('futureOperation', [
            'method' => 'get',
            'path' => '/future',
            'tag' => 'Future',
            'authenticated' => false,
            'scopes' => [],
            'request_content_types' => [],
            'response_statuses' => ['200'],
            'capabilities' => ['future_protocol_flag'],
        ]);
        self::assertTrue($future->supports('future_protocol_flag'));
    }
}

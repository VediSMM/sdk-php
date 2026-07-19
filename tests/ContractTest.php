<?php

declare(strict_types=1);

namespace VediSMM\Tests;

use PHPUnit\Framework\TestCase;
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
        self::assertSame(83, $manifest['operation_count'] ?? null);
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

        self::assertCount(83, $actual);
        self::assertSame($expected, $actual);
        self::assertSame([], array_filter(
            OperationCatalog::all(),
            static fn(array $operation): bool => str_starts_with($operation['path'], '/admin'),
        ));
    }
}

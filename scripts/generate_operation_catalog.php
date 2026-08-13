<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$manifestPath = $root . '/contract/sdk-operations.json';
$targetPath = $root . '/src/OperationCatalog.php';
$check = ($argv[1] ?? null) === '--check';
if (isset($argv[1]) && !$check) {
    fwrite(STDERR, "Usage: php scripts/generate_operation_catalog.php [--check]\n");
    exit(2);
}

$raw = file_get_contents($manifestPath);
if (!is_string($raw)) {
    fwrite(STDERR, "Unable to read contract/sdk-operations.json\n");
    exit(1);
}

try {
    $manifest = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    fwrite(STDERR, 'Invalid operation manifest: ' . $exception->getMessage() . "\n");
    exit(1);
}
if (!is_array($manifest) || !is_array($manifest['operations'] ?? null)) {
    fwrite(STDERR, "Operation manifest must contain an operations array\n");
    exit(1);
}
if (($manifest['operation_count'] ?? null) !== count($manifest['operations'])) {
    fwrite(STDERR, "Operation manifest count does not match operations\n");
    exit(1);
}

$operationIds = [];
$entries = [];
foreach ($manifest['operations'] as $operation) {
    if (!is_array($operation)) {
        fwrite(STDERR, "Every operation must be an object\n");
        exit(1);
    }
    $id = $operation['operation_id'] ?? null;
    if (!is_string($id) || $id === '' || isset($operationIds[$id])) {
        fwrite(STDERR, "Operation IDs must be non-empty and unique\n");
        exit(1);
    }
    $operationIds[$id] = true;
    foreach (['method', 'path', 'tag'] as $field) {
        if (!is_string($operation[$field] ?? null)) {
            fwrite(STDERR, sprintf("Operation %s has an invalid %s\n", $id, $field));
            exit(1);
        }
    }
    if (!is_bool($operation['authenticated'] ?? null)) {
        fwrite(STDERR, sprintf("Operation %s has an invalid authenticated flag\n", $id));
        exit(1);
    }
    foreach (['scopes', 'request_content_types', 'response_statuses', 'capabilities'] as $field) {
        if (!is_array($operation[$field] ?? null)) {
            fwrite(STDERR, sprintf("Operation %s has an invalid %s list\n", $id, $field));
            exit(1);
        }
        foreach ($operation[$field] as $value) {
            if (!is_string($value)) {
                fwrite(STDERR, sprintf("Operation %s has a non-string %s value\n", $id, $field));
                exit(1);
            }
        }
    }
    $entries[] = $operation;
}

$json = static fn(string $value): string => json_encode(
    $value,
    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
);
$list = static fn(array $values): string => json_encode(
    array_values($values),
    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
);

$lines = [
    '<?php',
    '',
    'declare(strict_types=1);',
    '',
    'namespace VediSMM;',
    '',
    'use VediSMM\\Exception\\ConfigurationException;',
    '',
    '/** Generated from contract/sdk-operations.json. */',
    'final class OperationCatalog',
    '{',
    '    /** @var array<string, array{method: string, path: string, tag: string, authenticated: bool, scopes: list<string>, request_content_types: list<string>, response_statuses: list<string>, capabilities: list<string>}> */',
    '    private const OPERATIONS = [',
];
foreach ($entries as $operation) {
    $lines[] = '        ' . $json($operation['operation_id']) . ' => [';
    $lines[] = "            'method' => " . $json($operation['method']) . ',';
    $lines[] = "            'path' => " . $json($operation['path']) . ',';
    $lines[] = "            'tag' => " . $json($operation['tag']) . ',';
    $lines[] = "            'authenticated' => " . ($operation['authenticated'] ? 'true' : 'false') . ',';
    $lines[] = "            'scopes' => " . $list($operation['scopes']) . ',';
    $lines[] = "            'request_content_types' => " . $list($operation['request_content_types']) . ',';
    $lines[] = "            'response_statuses' => " . $list($operation['response_statuses']) . ',';
    $lines[] = "            'capabilities' => " . $list($operation['capabilities']) . ',';
    $lines[] = '        ],';
}
$lines = array_merge($lines, [
    '    ];',
    '',
    '    /** @return array<string, array{method: string, path: string, tag: string, authenticated: bool, scopes: list<string>, request_content_types: list<string>, response_statuses: list<string>, capabilities: list<string>}> */',
    '    public static function all(): array',
    '    {',
    '        return self::OPERATIONS;',
    '    }',
    '',
    '    public static function get(string $operationId): Operation',
    '    {',
    '        $definition = self::OPERATIONS[$operationId] ?? null;',
    '        if ($definition === null) {',
    "            throw new ConfigurationException('Unknown VediSMM operation: ' . \$operationId);",
    '        }',
    '',
    '        return Operation::fromArray($operationId, $definition);',
    '    }',
    '}',
    '',
]);
$generated = implode("\n", $lines);

if ($check) {
    $committed = file_get_contents($targetPath);
    if (!is_string($committed) || !hash_equals($generated, $committed)) {
        fwrite(STDERR, "Operation catalog is stale; run composer operations:sync\n");
        exit(1);
    }
    fwrite(STDOUT, sprintf("Operation catalog matches %d manifest operations.\n", count($entries)));
    exit(0);
}

if (file_put_contents($targetPath, $generated) === false) {
    fwrite(STDERR, "Unable to write src/OperationCatalog.php\n");
    exit(1);
}
fwrite(STDOUT, sprintf("Generated %d operations in src/OperationCatalog.php.\n", count($entries)));

<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use VediSMM\OperationCatalog;
use VediSMM\VediSMM;

$root = dirname(__DIR__);
$required = [
    'README.md',
    'README.ru.md',
    'docs/en/guide.md',
    'docs/ru/guide.md',
    'docs/en/api-reference.md',
    'docs/ru/api-reference.md',
    'examples/quickstart.php',
    'CHANGELOG.md',
    'SECURITY.md',
    'CONTRIBUTING.md',
    'LICENSE',
];

foreach ($required as $file) {
    $path = $root . '/' . $file;
    $contents = file_get_contents($path);
    if (!is_string($contents) || trim($contents) === '') {
        fwrite(STDERR, sprintf("Missing or empty documentation file: %s\n", $file));
        exit(1);
    }
    if (preg_match('/\b(?:TBD|TODO|FIXME)\b/', $contents) === 1) {
        fwrite(STDERR, sprintf("Placeholder found in %s\n", $file));
        exit(1);
    }
}

$english = file_get_contents($root . '/docs/en/api-reference.md');
$russian = file_get_contents($root . '/docs/ru/api-reference.md');
if (!is_string($english) || !is_string($russian)) {
    fwrite(STDERR, "Unable to read API references\n");
    exit(1);
}

$catalog = OperationCatalog::all();
$ownership = VediSMM::operationOwnership();
$owned = [];
foreach ($ownership as $operations) {
    foreach ($operations as $operation) {
        if (isset($owned[$operation])) {
            fwrite(STDERR, sprintf("Duplicate operation ownership: %s\n", $operation));
            exit(1);
        }
        $owned[$operation] = true;
    }
}
if (count($catalog) !== 83 || count($owned) !== 83 || array_diff(array_keys($catalog), array_keys($owned)) !== []) {
    fwrite(STDERR, "Operation catalog and service ownership differ\n");
    exit(1);
}
foreach (array_keys($catalog) as $operation) {
    $needle = '`' . $operation . '(';
    if (!str_contains($english, $needle) || !str_contains($russian, $needle)) {
        fwrite(STDERR, sprintf("Missing API reference entry: %s\n", $operation));
        exit(1);
    }
}

fwrite(STDOUT, "Documentation covers all 83 operations in English and Russian.\n");

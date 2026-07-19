<?php

declare(strict_types=1);

$directory = dirname(__DIR__) . '/dist';
if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
    fwrite(STDERR, "Unable to create dist directory\n");
    exit(1);
}
$artifact = $directory . '/vedismm-sdk.zip';
if (is_file($artifact) && !unlink($artifact)) {
    fwrite(STDERR, "Unable to replace existing artifact\n");
    exit(1);
}

<?php

declare(strict_types=1);

require \dirname(__DIR__) . '/vendor/autoload.php';

use VediSMM\Client;
use VediSMM\Config;
use VediSMM\VediSMM;

$token = getenv('VEDISMM_TOKEN');
if (!\is_string($token) || $token === '') {
    fwrite(STDERR, "Set VEDISMM_TOKEN before running this example.\n");
    exit(2);
}

$sdk = new VediSMM(new Client(new Config(accessToken: $token)));
$response = $sdk->profile->getMe();
$profile = \is_array($response->data) ? ($response->data['data'] ?? null) : null;

echo json_encode($profile, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

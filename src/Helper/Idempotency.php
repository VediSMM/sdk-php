<?php

declare(strict_types=1);

namespace VediSMM\Helper;

use Closure;
use VediSMM\Exception\ConfigurationException;

final class Idempotency
{
    /** @param null|callable(int): string $randomBytes */
    public static function key(?callable $randomBytes = null): string
    {
        $random = $randomBytes === null ? random_bytes(...) : Closure::fromCallable($randomBytes);
        $bytes = $random(16);
        if (\strlen($bytes) !== 16) {
            throw new ConfigurationException('idempotency random source must return exactly 16 bytes');
        }
        $bytes[6] = \chr((\ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = \chr((\ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return \sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}

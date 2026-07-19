<?php

declare(strict_types=1);

namespace VediSMM;

final class Redactor
{
    /** @param list<string> $secrets */
    public static function text(string $value, array $secrets = []): string
    {
        foreach ($secrets as $secret) {
            if ($secret !== '') {
                $value = str_replace($secret, '[REDACTED]', $value);
            }
        }
        $value = preg_replace('/Bearer\s+[A-Za-z0-9._~+\/-]+=*/i', 'Bearer [REDACTED]', $value) ?? $value;
        $value = preg_replace('/("(?:access_token|refresh_token|token|secret|password)"\s*:\s*")[^"]*(")/i', '$1[REDACTED]$2', $value) ?? $value;
        $value = preg_replace('/((?:access_token|refresh_token|token|secret|password)=)[^&\s]*/i', '$1[REDACTED]', $value) ?? $value;

        return substr($value, 0, 4096);
    }

    /** @param list<string> $secrets */
    public static function value(mixed $value, array $secrets = [], int $depth = 0): mixed
    {
        if ($depth > 8) {
            return '[TRUNCATED]';
        }
        if (\is_string($value)) {
            return self::text($value, $secrets);
        }
        if (!\is_array($value)) {
            return $value;
        }
        $safe = [];
        $count = 0;
        foreach ($value as $key => $item) {
            if (++$count > 100) {
                break;
            }
            $safe[$key] = \is_string($key) && preg_match('/token|secret|password|authorization/i', $key) === 1
                ? '[REDACTED]'
                : self::value($item, $secrets, $depth + 1);
        }

        return $safe;
    }
}

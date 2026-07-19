<?php

declare(strict_types=1);

namespace VediSMM;

use Closure;
use VediSMM\Exception\ConfigurationException;

final class Config
{
    public const DEFAULT_BASE_URL = 'https://vedismm.ru/api/v1';

    private readonly ?string $accessToken;

    /** @var null|Closure(): string */
    private readonly ?Closure $tokenProvider;

    /** @var Closure(int): void */
    private readonly Closure $sleeper;

    /** @var Closure(): float */
    private readonly Closure $random;

    /**
     * @param string|callable(): string|null $accessToken
     * @param null|callable(int): void $sleeper Receives milliseconds.
     * @param null|callable(): float $random Returns a number from 0 through 1.
     */
    public function __construct(
        string|callable|null $accessToken = null,
        public readonly string $baseUrl = self::DEFAULT_BASE_URL,
        public readonly int $timeoutMs = 30000,
        public readonly int $maxRetries = 2,
        public readonly int $retryBaseDelayMs = 200,
        public readonly int $maxResponseBytes = 16777216,
        public readonly int $maxErrorBytes = 65536,
        ?callable $sleeper = null,
        ?callable $random = null,
    ) {
        $normalized = self::normalizeBaseUrl($baseUrl);
        if ($normalized !== $baseUrl) {
            throw new ConfigurationException('baseUrl must be normalized without a trailing slash');
        }
        self::positive('timeoutMs', $timeoutMs);
        self::positive('maxRetries', $maxRetries, true);
        self::positive('retryBaseDelayMs', $retryBaseDelayMs);
        self::positive('maxResponseBytes', $maxResponseBytes);
        self::positive('maxErrorBytes', $maxErrorBytes);

        $this->accessToken = \is_string($accessToken) ? self::validateToken($accessToken) : null;
        $this->tokenProvider = \is_callable($accessToken) && !\is_string($accessToken)
            ? Closure::fromCallable($accessToken)
            : null;
        $this->sleeper = $sleeper === null
            ? static function (int $milliseconds): void {
                usleep($milliseconds * 1000);
            }
        : Closure::fromCallable($sleeper);
        $this->random = $random === null
            ? static fn(): float => mt_rand() / mt_getrandmax()
            : Closure::fromCallable($random);
    }

    public function token(): ?string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }
        if ($this->tokenProvider === null) {
            return null;
        }

        return self::validateToken(($this->tokenProvider)());
    }

    public function sleep(int $milliseconds): void
    {
        ($this->sleeper)($milliseconds);
    }

    public function jitter(): float
    {
        $value = ($this->random)();
        if (!is_finite($value) || $value < 0 || $value > 1) {
            throw new ConfigurationException('random callback must return a number from 0 through 1');
        }

        return $value;
    }

    public function __toString(): string
    {
        return \sprintf('VediSMM\\Config(%s)', $this->baseUrl);
    }

    private static function normalizeBaseUrl(string $raw): string
    {
        $parts = parse_url($raw);
        if (!\is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            throw new ConfigurationException('baseUrl must be an absolute HTTP(S) URL');
        }
        $scheme = strtolower((string) $parts['scheme']);
        $host = strtolower((string) $parts['host']);
        $local = \in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if ($scheme !== 'https' && !($scheme === 'http' && $local)) {
            throw new ConfigurationException('baseUrl must use HTTPS (HTTP is allowed only for localhost)');
        }
        foreach (['user', 'pass', 'query', 'fragment'] as $forbidden) {
            if (\array_key_exists($forbidden, $parts)) {
                throw new ConfigurationException('baseUrl must not contain credentials, query, or fragment');
            }
        }

        return rtrim($raw, '/');
    }

    private static function positive(string $name, int $value, bool $allowZero = false): void
    {
        if ($allowZero ? $value < 0 : $value <= 0) {
            throw new ConfigurationException(\sprintf('%s must be %s integer', $name, $allowZero ? 'a non-negative' : 'a positive'));
        }
    }

    private static function validateToken(string $token): string
    {
        if ($token === '' || preg_match('/[\x00-\x20\x7F]/', $token) === 1) {
            throw new ConfigurationException('access token must be non-empty and contain no control or whitespace characters');
        }

        return $token;
    }
}

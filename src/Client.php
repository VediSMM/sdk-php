<?php

declare(strict_types=1);

namespace VediSMM;

use JsonException;
use VediSMM\Exception\ApiException;
use VediSMM\Exception\ConfigurationException;
use VediSMM\Exception\DecodeException;
use VediSMM\Exception\PreconditionFailedException;
use VediSMM\Exception\RateLimitException;
use VediSMM\Exception\RedirectException;
use VediSMM\Exception\TimeoutException;
use VediSMM\Exception\TransportException;
use VediSMM\Transport\CurlTransport;
use VediSMM\Transport\TransportInterface;
use VediSMM\Transport\TransportRequest;
use VediSMM\Transport\TransportResponse;
use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class Client
{
    /** @var list<int> */
    private const RETRY_STATUSES = [429, 502, 503, 504];

    public function __construct(
        public readonly Config $config = new Config(),
        private readonly TransportInterface $transport = new CurlTransport(),
    ) {}

    /** @return ApiResponse<mixed> */
    public function call(string $operationId, ?CallOptions $options = null): ApiResponse
    {
        $operation = OperationCatalog::get($operationId);
        $options ??= new CallOptions();
        $token = $operation->authenticated ? $this->config->token() : null;
        $url = $this->buildUrl($operation, $options);
        [$headers, $body] = $this->prepareRequest($operation, $options, $token);
        $timeoutMs = $options->timeoutMs ?? $this->config->timeoutMs;
        if ($timeoutMs <= 0) {
            throw new ConfigurationException('timeoutMs must be a positive integer');
        }
        $replayable = !\is_resource($body) && ($options->multipart === null || $options->replayable);
        $canRetry = \in_array($operation->method, ['get', 'put', 'delete'], true)
            || $options->idempotencyKey !== null;
        $secrets = $token === null ? [] : [$token];

        for ($attempt = 0; ; ++$attempt) {
            try {
                $response = $this->transport->send(new TransportRequest(
                    $operation->method,
                    $url,
                    $headers,
                    $body,
                    $timeoutMs,
                    $options->sink === null ? $this->config->maxResponseBytes : PHP_INT_MAX,
                    $options->sink,
                ));
            } catch (TransportException $exception) {
                if (!$canRetry || !$replayable || $attempt >= $this->config->maxRetries) {
                    throw $this->sanitizeTransportException($exception, $secrets);
                }
                $this->backoff($attempt, null);
                continue;
            }

            if ($response->status >= 300 && $response->status < 400) {
                throw new RedirectException($response->status, self::header($response, 'request-id'));
            }
            if (\in_array($response->status, self::RETRY_STATUSES, true)
                && $canRetry
                && $replayable
                && $attempt < $this->config->maxRetries) {
                $this->backoff($attempt, self::retryAfterMs($response));
                continue;
            }

            return $this->decode($response, $options, $secrets);
        }
    }

    public function __toString(): string
    {
        return \sprintf('VediSMM\\Client(%s)', $this->config->baseUrl);
    }

    private function buildUrl(Operation $operation, CallOptions $options): string
    {
        $used = [];
        $path = preg_replace_callback('/\{([^}]+)\}/', static function (array $match) use ($options, &$used): string {
            $name = $match[1];
            $value = $options->path[$name] ?? null;
            if ($value === null || (string) $value === '') {
                throw new ConfigurationException('missing path parameter: ' . $name);
            }
            $used[$name] = true;

            return rawurlencode((string) $value);
        }, $operation->path);
        if ($path === null) {
            throw new ConfigurationException('invalid operation path template');
        }
        $unused = array_diff(array_keys($options->path), array_keys($used));
        if ($unused !== []) {
            throw new ConfigurationException('unused path parameter: ' . implode(', ', $unused));
        }

        $queryParts = [];
        foreach ($options->query as $name => $raw) {
            $values = \is_array($raw) ? $raw : [$raw];
            foreach ($values as $value) {
                $queryParts[] = rawurlencode($name) . '=' . rawurlencode($value === null ? '' : self::scalar($value));
            }
        }

        return $this->config->baseUrl . $path . ($queryParts === [] ? '' : '?' . implode('&', $queryParts));
    }

    /** @return array{array<string, string>, mixed} */
    private function prepareRequest(Operation $operation, CallOptions $options, ?string $token): array
    {
        $headers = ['Accept' => 'application/json, application/problem+json'];
        foreach ($options->headers as $name => $value) {
            if (preg_match('/^[A-Za-z0-9!#$%&\'*+.^_`|~-]+$/D', $name) !== 1
                || preg_match('/[\x00-\x1F\x7F]/D', $value) === 1) {
                throw new ConfigurationException('invalid request header');
            }
            if (\in_array(strtolower($name), ['authorization', 'x-api-token', 'host', 'content-length'], true)) {
                throw new ConfigurationException('reserved request header: ' . $name);
            }
            $headers[$name] = $value;
        }
        if ($token !== null) {
            $headers['Authorization'] = 'Bearer ' . $token;
            $headers['X-API-Token'] = $token;
        }
        if ($options->idempotencyKey !== null) {
            self::validateHeaderValue('idempotencyKey', $options->idempotencyKey);
            $headers['Idempotency-Key'] = $options->idempotencyKey;
        }
        if ($options->ifMatch !== null) {
            self::validateHeaderValue('ifMatch', $options->ifMatch);
            $headers['If-Match'] = $options->ifMatch;
        }

        if ($options->multipart !== null) {
            return [$headers, $options->multipart];
        }
        if ($options->rawBody !== null) {
            return [$headers, $options->rawBody];
        }
        if ($options->body === null) {
            return [$headers, null];
        }
        try {
            $body = json_encode($options->body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new ConfigurationException('request body is not valid JSON data', null, $exception);
        }
        $headers['Content-Type'] = 'application/json';

        return [$headers, $body];
    }

    /** @param list<string> $secrets
     *  @return ApiResponse<mixed>
     */
    private function decode(TransportResponse $response, CallOptions $options, array $secrets): ApiResponse
    {
        $requestId = self::header($response, 'request-id');
        $etag = self::header($response, 'etag');
        $parsed = null;
        if ($options->sink === null && $response->body !== '') {
            try {
                $parsed = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                if ($response->status >= 200 && $response->status < 300) {
                    throw new DecodeException('VediSMM API returned invalid JSON', $requestId, $exception);
                }
                $parsed = null;
            }
        }

        if ($response->status >= 200 && $response->status < 300) {
            return new ApiResponse($response->status, $response->headers, $parsed, $requestId, $etag);
        }

        $problem = \is_array($parsed) ? $parsed : [];
        $code = Redactor::text(\is_string($problem['code'] ?? null) ? $problem['code'] : 'http_' . $response->status, $secrets);
        $detail = \is_string($problem['detail'] ?? null)
            ? Redactor::text($problem['detail'], $secrets)
            : Redactor::text(substr($response->body, 0, $this->config->maxErrorBytes), $secrets);
        if ($detail === '') {
            $detail = 'HTTP ' . $response->status;
        }
        $problemRequestId = \is_string($problem['request_id'] ?? null) ? $problem['request_id'] : $requestId;
        $errors = Redactor::value($problem['errors'] ?? null, $secrets);
        $retryAfterMs = self::retryAfterMs($response);
        $arguments = [$response->status, $code, $detail, $errors, $problemRequestId, $retryAfterMs];
        if ($response->status === 429) {
            throw new RateLimitException(...$arguments);
        }
        if ($response->status === 412) {
            throw new PreconditionFailedException(...$arguments);
        }
        throw new ApiException(...$arguments);
    }

    private function backoff(int $attempt, ?int $retryAfterMs): void
    {
        if ($retryAfterMs !== null) {
            $this->config->sleep($retryAfterMs);

            return;
        }
        $base = min(30000, $this->config->retryBaseDelayMs * (2 ** $attempt));
        $this->config->sleep((int) round($base * (0.5 + 0.5 * $this->config->jitter())));
    }

    /** @param list<string> $secrets */
    private function sanitizeTransportException(TransportException $exception, array $secrets): TransportException
    {
        $message = Redactor::text($exception->getMessage(), $secrets);
        if ($exception instanceof TimeoutException) {
            return new TimeoutException($message, $exception->requestId, $exception);
        }

        return new TransportException($message, $exception->requestId, $exception);
    }

    private static function retryAfterMs(TransportResponse $response): ?int
    {
        $raw = self::header($response, 'retry-after');
        if ($raw === null) {
            return null;
        }
        if (ctype_digit($raw)) {
            return min(30000, (int) $raw * 1000);
        }
        $timestamp = strtotime($raw);

        return $timestamp === false ? null : max(0, min(30000, ($timestamp - time()) * 1000));
    }

    private static function header(TransportResponse $response, string $name): ?string
    {
        foreach ($response->headers as $header => $value) {
            if (strtolower($header) === $name) {
                return $value;
            }
        }

        return null;
    }

    private static function scalar(bool|float|int|string $value): string
    {
        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    private static function validateHeaderValue(string $name, string $value): void
    {
        if ($value === '' || preg_match('/[\x00-\x20\x7F]/D', $value) === 1) {
            throw new ConfigurationException($name . ' must be non-empty and contain no control or whitespace characters');
        }
    }
}

<?php

declare(strict_types=1);

namespace VediSMM\Transport;

use VediSMM\Exception\DecodeException;
use VediSMM\Exception\TimeoutException;
use VediSMM\Exception\TransportException;
use VediSMM\Redactor;

final class CurlTransport implements TransportInterface
{
    public function send(TransportRequest $request): TransportResponse
    {
        $handle = curl_init($request->url);
        if ($handle === false) {
            throw new TransportException('Unable to initialize cURL');
        }

        /** @var array<string, string> $responseHeaders */
        $responseHeaders = [];
        $responseBody = '';
        $responseSize = 0;
        $tooLarge = false;
        $headers = [];
        foreach ($request->headers as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => strtoupper($request->method),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_CONNECTTIMEOUT_MS => min(10000, $request->timeoutMs),
            CURLOPT_TIMEOUT_MS => $request->timeoutMs,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $length = \strlen($line);
                $separator = strpos($line, ':');
                if ($separator !== false) {
                    $name = strtolower(trim(substr($line, 0, $separator)));
                    if ($name !== '') {
                        $responseHeaders[$name] = trim(substr($line, $separator + 1));
                    }
                }

                return $length;
            },
            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use ($request, &$responseBody, &$responseSize, &$tooLarge): int {
                $length = \strlen($chunk);
                $responseSize += $length;
                if ($responseSize > $request->maxResponseBytes) {
                    $tooLarge = true;

                    return 0;
                }
                if (\is_resource($request->sink)) {
                    $written = fwrite($request->sink, $chunk);

                    return $written === false ? 0 : $written;
                }
                $responseBody .= $chunk;

                return $length;
            },
        ]);

        if (\is_resource($request->body)) {
            curl_setopt($handle, CURLOPT_UPLOAD, true);
            curl_setopt($handle, CURLOPT_READFUNCTION, static fn($curl, $stream, int $length): string => fread($request->body, max(1, $length)) ?: '');
            $stats = fstat($request->body);
            if ($stats !== false) {
                curl_setopt($handle, CURLOPT_INFILESIZE, $stats['size']);
            }
        } elseif (\is_string($request->body) || \is_array($request->body)) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $request->body);
        } elseif ($request->body !== null) {
            curl_close($handle);
            throw new TransportException('request body must be a string, stream resource, or multipart field array');
        }

        try {
            $result = curl_exec($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            $errorNumber = curl_errno($handle);
            $error = curl_error($handle);
        } finally {
            curl_close($handle);
        }

        if ($tooLarge) {
            throw new DecodeException(\sprintf('response exceeds configured limit of %d bytes', $request->maxResponseBytes));
        }
        if ($result === false || $errorNumber !== 0) {
            $message = Redactor::text($error === '' ? 'Unknown cURL failure' : $error);
            if ($errorNumber === CURLE_OPERATION_TIMEDOUT) {
                throw new TimeoutException('VediSMM API request timed out: ' . $message);
            }
            throw new TransportException('VediSMM API transport failed: ' . $message);
        }
        if ($status === 0) {
            throw new TransportException('VediSMM API transport returned no HTTP status');
        }

        return new TransportResponse($status, $responseHeaders, $responseBody);
    }
}

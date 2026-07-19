<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Client;
use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

abstract class AbstractService
{
    public function __construct(protected readonly Client $client) {}

    /** @return ApiResponse<mixed> */
    final protected function call(string $operationId, ?CallOptions $options): ApiResponse
    {
        return $this->client->call($operationId, $options);
    }
}

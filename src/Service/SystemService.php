<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class SystemService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function getOpenApi(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getOpenApi', $options);
    }

    /** @return ApiResponse<mixed> */
    public function ping(?CallOptions $options = null): ApiResponse
    {
        return $this->call('ping', $options);
    }
}

<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class ConnectionsService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function cancelAccountConnection(?CallOptions $options = null): ApiResponse
    {
        return $this->call('cancelAccountConnection', $options);
    }

    /** @return ApiResponse<mixed> */
    public function confirmAccountConnection(?CallOptions $options = null): ApiResponse
    {
        return $this->call('confirmAccountConnection', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getAccountConnection(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getAccountConnection', $options);
    }

    /** @return ApiResponse<mixed> */
    public function startAccountConnection(?CallOptions $options = null): ApiResponse
    {
        return $this->call('startAccountConnection', $options);
    }
}

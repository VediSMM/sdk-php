<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class SessionsService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function getSession(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getSession', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listSessions(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listSessions', $options);
    }

    /** @return ApiResponse<mixed> */
    public function revokeSession(?CallOptions $options = null): ApiResponse
    {
        return $this->call('revokeSession', $options);
    }
}

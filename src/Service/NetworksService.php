<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class NetworksService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function getNetwork(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getNetwork', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listNetworks(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listNetworks', $options);
    }
}

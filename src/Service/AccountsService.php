<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class AccountsService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function disconnectAccount(?CallOptions $options = null): ApiResponse
    {
        return $this->call('disconnectAccount', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getAccount(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getAccount', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listAccounts(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listAccounts', $options);
    }

    /** @return ApiResponse<mixed> */
    public function verifyAccount(?CallOptions $options = null): ApiResponse
    {
        return $this->call('verifyAccount', $options);
    }
}

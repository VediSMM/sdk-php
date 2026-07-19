<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class ProfileService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function changePassword(?CallOptions $options = null): ApiResponse
    {
        return $this->call('changePassword', $options);
    }

    /** @return ApiResponse<mixed> */
    public function deleteMe(?CallOptions $options = null): ApiResponse
    {
        return $this->call('deleteMe', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getMe(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getMe', $options);
    }

    /** @return ApiResponse<mixed> */
    public function updateMe(?CallOptions $options = null): ApiResponse
    {
        return $this->call('updateMe', $options);
    }
}

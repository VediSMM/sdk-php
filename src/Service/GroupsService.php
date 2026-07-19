<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class GroupsService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function createGroup(?CallOptions $options = null): ApiResponse
    {
        return $this->call('createGroup', $options);
    }

    /** @return ApiResponse<mixed> */
    public function deleteGroup(?CallOptions $options = null): ApiResponse
    {
        return $this->call('deleteGroup', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getGroup(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getGroup', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listGroups(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listGroups', $options);
    }

    /** @return ApiResponse<mixed> */
    public function replaceGroupAccounts(?CallOptions $options = null): ApiResponse
    {
        return $this->call('replaceGroupAccounts', $options);
    }

    /** @return ApiResponse<mixed> */
    public function updateGroup(?CallOptions $options = null): ApiResponse
    {
        return $this->call('updateGroup', $options);
    }
}

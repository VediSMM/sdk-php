<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class PersonalTokensService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function createPersonalToken(?CallOptions $options = null): ApiResponse
    {
        return $this->call('createPersonalToken', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getPersonalToken(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getPersonalToken', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listPersonalTokens(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listPersonalTokens', $options);
    }

    /** @return ApiResponse<mixed> */
    public function revokePersonalToken(?CallOptions $options = null): ApiResponse
    {
        return $this->call('revokePersonalToken', $options);
    }

    /** @return ApiResponse<mixed> */
    public function rotatePersonalToken(?CallOptions $options = null): ApiResponse
    {
        return $this->call('rotatePersonalToken', $options);
    }

    /** @return ApiResponse<mixed> */
    public function updatePersonalToken(?CallOptions $options = null): ApiResponse
    {
        return $this->call('updatePersonalToken', $options);
    }
}

<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class AuthService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function forgotPassword(?CallOptions $options = null): ApiResponse
    {
        return $this->call('forgotPassword', $options);
    }

    /** @return ApiResponse<mixed> */
    public function login(?CallOptions $options = null): ApiResponse
    {
        return $this->call('login', $options);
    }

    /** @return ApiResponse<mixed> */
    public function logout(?CallOptions $options = null): ApiResponse
    {
        return $this->call('logout', $options);
    }

    /** @return ApiResponse<mixed> */
    public function logoutAll(?CallOptions $options = null): ApiResponse
    {
        return $this->call('logoutAll', $options);
    }

    /** @return ApiResponse<mixed> */
    public function refresh(?CallOptions $options = null): ApiResponse
    {
        return $this->call('refresh', $options);
    }

    /** @return ApiResponse<mixed> */
    public function register(?CallOptions $options = null): ApiResponse
    {
        return $this->call('register', $options);
    }

    /** @return ApiResponse<mixed> */
    public function resendVerification(?CallOptions $options = null): ApiResponse
    {
        return $this->call('resendVerification', $options);
    }

    /** @return ApiResponse<mixed> */
    public function resetPassword(?CallOptions $options = null): ApiResponse
    {
        return $this->call('resetPassword', $options);
    }

    /** @return ApiResponse<mixed> */
    public function verifyEmail(?CallOptions $options = null): ApiResponse
    {
        return $this->call('verifyEmail', $options);
    }
}

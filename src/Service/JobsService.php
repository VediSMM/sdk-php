<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class JobsService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function deletePostEverywhere(?CallOptions $options = null): ApiResponse
    {
        return $this->call('deletePostEverywhere', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getPublicationJob(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getPublicationJob', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listPublicationJobs(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listPublicationJobs', $options);
    }

    /** @return ApiResponse<mixed> */
    public function publishPost(?CallOptions $options = null): ApiResponse
    {
        return $this->call('publishPost', $options);
    }

    /** @return ApiResponse<mixed> */
    public function retryPostTargets(?CallOptions $options = null): ApiResponse
    {
        return $this->call('retryPostTargets', $options);
    }
}

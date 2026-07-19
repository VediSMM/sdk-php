<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class MediaService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function deleteMedia(?CallOptions $options = null): ApiResponse
    {
        return $this->call('deleteMedia', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getMedia(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getMedia', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getMediaContent(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getMediaContent', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getSignedMediaContent(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getSignedMediaContent', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listMedia(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listMedia', $options);
    }

    /** @return ApiResponse<mixed> */
    public function uploadMedia(?CallOptions $options = null): ApiResponse
    {
        return $this->call('uploadMedia', $options);
    }
}

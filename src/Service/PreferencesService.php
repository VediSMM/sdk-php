<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class PreferencesService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function createContentTemplate(?CallOptions $options = null): ApiResponse
    {
        return $this->call('createContentTemplate', $options);
    }

    /** @return ApiResponse<mixed> */
    public function deleteContentTemplate(?CallOptions $options = null): ApiResponse
    {
        return $this->call('deleteContentTemplate', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getContentTemplate(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getContentTemplate', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getSignatures(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getSignatures', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listContentTemplates(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listContentTemplates', $options);
    }

    /** @return ApiResponse<mixed> */
    public function replaceSignatures(?CallOptions $options = null): ApiResponse
    {
        return $this->call('replaceSignatures', $options);
    }

    /** @return ApiResponse<mixed> */
    public function updateContentTemplate(?CallOptions $options = null): ApiResponse
    {
        return $this->call('updateContentTemplate', $options);
    }
}

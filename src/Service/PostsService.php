<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class PostsService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function checkPostConstraints(?CallOptions $options = null): ApiResponse
    {
        return $this->call('checkPostConstraints', $options);
    }

    /** @return ApiResponse<mixed> */
    public function createPostDraft(?CallOptions $options = null): ApiResponse
    {
        return $this->call('createPostDraft', $options);
    }

    /** @return ApiResponse<mixed> */
    public function deletePostDraft(?CallOptions $options = null): ApiResponse
    {
        return $this->call('deletePostDraft', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getPost(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getPost', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listPosts(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listPosts', $options);
    }

    /** @return ApiResponse<mixed> */
    public function schedulePost(?CallOptions $options = null): ApiResponse
    {
        return $this->call('schedulePost', $options);
    }

    /** @return ApiResponse<mixed> */
    public function unschedulePost(?CallOptions $options = null): ApiResponse
    {
        return $this->call('unschedulePost', $options);
    }

    /** @return ApiResponse<mixed> */
    public function updatePostDraft(?CallOptions $options = null): ApiResponse
    {
        return $this->call('updatePostDraft', $options);
    }
}

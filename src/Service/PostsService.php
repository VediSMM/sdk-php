<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Exception\ConfigurationException;
use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

/**
 * @phpstan-type TrackingSettings array{shorten_links: bool, add_source: bool}
 * @phpstan-type PostTrackingRequestOptions array{tracking: TrackingSettings}
 * @phpstan-type PostCreateRequest array{title?: string, content?: string, link?: string|null, account_ids?: list<int>, group_ids?: list<int>, media_ids?: list<int>, content_overrides?: array<string, string>, append_signature?: bool, first_comment?: string, options?: PostTrackingRequestOptions}
 * @phpstan-type PostUpdateRequest array{title?: string, content?: string, link?: string|null, account_ids?: list<int>, group_ids?: list<int>, media_ids?: list<int>, content_overrides?: array<string, string>, append_signature?: bool, first_comment?: string, options?: PostTrackingRequestOptions}
 */
final class PostsService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function checkPostConstraints(?CallOptions $options = null): ApiResponse
    {
        return $this->call('checkPostConstraints', $options);
    }

    /**
     * @param PostCreateRequest|CallOptions|null $options
     * @return ApiResponse<mixed>
     */
    public function createPostDraft(array|CallOptions|null $options = null, ?CallOptions $callOptions = null): ApiResponse
    {
        return $this->call('createPostDraft', self::requestOptions($options, $callOptions));
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

    /**
     * @param PostUpdateRequest|CallOptions|null $options
     * @return ApiResponse<mixed>
     */
    public function updatePostDraft(array|CallOptions|null $options = null, ?CallOptions $callOptions = null): ApiResponse
    {
        return $this->call('updatePostDraft', self::requestOptions($options, $callOptions));
    }

    /** @param array<string, mixed>|CallOptions|null $request */
    private static function requestOptions(array|CallOptions|null $request, ?CallOptions $callOptions): ?CallOptions
    {
        if ($request instanceof CallOptions) {
            if ($callOptions !== null) {
                throw new ConfigurationException('callOptions must be omitted when the first argument is CallOptions');
            }

            return $request;
        }
        if ($request === null) {
            return $callOptions;
        }

        return ($callOptions ?? new CallOptions())->with(body: $request, replaceBody: true);
    }
}

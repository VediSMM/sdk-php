<?php

declare(strict_types=1);

namespace VediSMM\Service;

use Generator;
use VediSMM\Helper\Paginator;
use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

/**
 * @phpstan-type TrackingLink array{id: int, code: string, short_url: string, destination_url: string, version: int, disabled_at: string|null, archived_at: string|null, created_at: string|null, updated_at: string|null}
 * @phpstan-type TrackingLinkCreateRequest array{destination_url: string}
 * @phpstan-type TrackingLinkResponse array{data: TrackingLink}
 * @phpstan-type TrackingLinkListQuery array{cursor?: string, limit?: int}
 * @phpstan-type TrackingLinkListResponse array{data: list<TrackingLink>, meta: array{next_cursor: string|null, has_more: bool, limit: int}}
 */
final class TrackingLinksService extends AbstractService
{
    /**
     * @param TrackingLinkCreateRequest $data
     * @return ApiResponse<TrackingLinkResponse>
     */
    public function create(array $data, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->call(
            'createTrackingLink',
            ($options ?? new CallOptions())->with(body: $data, replaceBody: true),
        );

        /** @var ApiResponse<TrackingLinkResponse> $response */
        return $response;
    }

    /**
     * @param TrackingLinkListQuery $query
     * @return ApiResponse<TrackingLinkListResponse>
     */
    public function list(array $query = [], ?CallOptions $options = null): ApiResponse
    {
        $response = $this->call('listTrackingLinks', ($options ?? new CallOptions())->with(query: $query));

        /** @var ApiResponse<TrackingLinkListResponse> $response */
        return $response;
    }

    /** @return ApiResponse<TrackingLinkResponse> */
    public function get(int $id, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->call('getTrackingLink', ($options ?? new CallOptions())->with(path: ['id' => $id]));

        /** @var ApiResponse<TrackingLinkResponse> $response */
        return $response;
    }

    /** @return ApiResponse<TrackingLinkResponse> */
    public function disable(int $id, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->call('disableTrackingLink', ($options ?? new CallOptions())->with(path: ['id' => $id]));

        /** @var ApiResponse<TrackingLinkResponse> $response */
        return $response;
    }

    /** @return ApiResponse<TrackingLinkResponse> */
    public function archive(int $id, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->call('archiveTrackingLink', ($options ?? new CallOptions())->with(path: ['id' => $id]));

        /** @var ApiResponse<TrackingLinkResponse> $response */
        return $response;
    }

    /**
     * @param TrackingLinkListQuery $query
     * @return Generator<int, TrackingLink, mixed, void>
     */
    public function iterate(array $query = [], ?CallOptions $options = null): Generator
    {
        $initialCursor = isset($query['cursor']) ? (string) $query['cursor'] : null;
        unset($query['cursor']);

        /** @var Generator<int, TrackingLink, mixed, void> */
        return Paginator::iterate(
            fn(?string $cursor): ApiResponse => $this->list(self::cursorQuery($query, $cursor), $options),
            $initialCursor,
        );
    }

    /**
     * @param TrackingLinkListQuery $query
     * @return TrackingLinkListQuery
     */
    private static function cursorQuery(array $query, ?string $cursor): array
    {
        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }

        return $query;
    }
}

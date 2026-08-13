<?php

declare(strict_types=1);

namespace VediSMM\Service;

use Generator;
use VediSMM\Helper\Paginator;
use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

/**
 * @phpstan-type TrackingNetwork 'vk'|'ok'|'telegram'|'instagram'|'facebook'|'x'|'tumblr'|'dzen'|'pinterest'|'linkedin'|'mastodon'|'bluesky'|'discord'|'threads'|'reddit'|'max'|'youtube'|'rutube'|'tiktok'
 * @phpstan-type TrackingAnalyticsQuery array{from: string, to: string, link_id?: int, post_id?: int, network?: TrackingNetwork}
 * @phpstan-type TrackingAnalyticsListQuery array{from: string, to: string, link_id?: int, post_id?: int, network?: TrackingNetwork, limit?: int, cursor?: string}
 * @phpstan-type TrackingAnalyticsFilter array{from: string, to: string, link_id: int|null, post_id: int|null, network: string|null}
 * @phpstan-type TrackingAnalyticsResourceMeta array{filter: TrackingAnalyticsFilter}
 * @phpstan-type TrackingAnalyticsPageMeta array{filter: TrackingAnalyticsFilter, next_cursor: string|null, has_more: bool, limit: int}
 * @phpstan-type TrackingAnalyticsSummary array{human_clicks: int, unique_visitors: int, unknown_bot_clicks: int, known_bot_clicks: int}
 * @phpstan-type TrackingAnalyticsTimeseriesRow array{date: string, human_clicks: int, unique_visitors: int, unknown_bot_clicks: int, known_bot_clicks: int}
 * @phpstan-type TrackingAnalyticsLinkRow array{link_id: int, code: string, post_id: int|null, post_target_id: int|null, network: string, human_clicks: int, unique_visitors: int, unknown_bot_clicks: int, known_bot_clicks: int}
 * @phpstan-type TrackingAnalyticsPostRow array{post_id: int, post_target_id: int|null, title: string|null, network: string, human_clicks: int, unique_visitors: int, unknown_bot_clicks: int, known_bot_clicks: int}
 * @phpstan-type TrackingAnalyticsSourceRow array{source_type: 'referer'|'utm_source'|'utm_medium'|'utm_campaign'|'utm_term'|'utm_content', value: string, human_clicks: int, unique_visitors: int, unknown_bot_clicks: int, known_bot_clicks: int}
 * @phpstan-type TrackingGeoCountry array{country_code: string, human_clicks: int}
 * @phpstan-type TrackingGeoFeatureProperties array{country_code: string, region_name: string, city_name: string, human_clicks: int}
 * @phpstan-type TrackingGeoFeature array{type: 'Feature', geometry: array{type: 'Point', coordinates: array{float|int, float|int}}, properties: TrackingGeoFeatureProperties}
 * @phpstan-type TrackingAnalyticsGeo array{countries: list<TrackingGeoCountry>, cities: array{type: 'FeatureCollection', features: list<TrackingGeoFeature>}}
 */
final class TrackingAnalyticsService extends AbstractService
{
    /**
     * @param TrackingAnalyticsQuery $query
     * @return ApiResponse<array{data: TrackingAnalyticsSummary, meta: TrackingAnalyticsResourceMeta}>
     */
    public function summary(array $query, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->request('getTrackingAnalyticsSummary', $query, $options);

        /** @var ApiResponse<array{data: TrackingAnalyticsSummary, meta: TrackingAnalyticsResourceMeta}> $response */
        return $response;
    }

    /**
     * @param TrackingAnalyticsQuery $query
     * @return ApiResponse<array{data: list<TrackingAnalyticsTimeseriesRow>, meta: TrackingAnalyticsResourceMeta}>
     */
    public function timeseries(array $query, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->request('getTrackingAnalyticsTimeseries', $query, $options);

        /** @var ApiResponse<array{data: list<TrackingAnalyticsTimeseriesRow>, meta: TrackingAnalyticsResourceMeta}> $response */
        return $response;
    }

    /**
     * @param TrackingAnalyticsListQuery $query
     * @return ApiResponse<array{data: list<TrackingAnalyticsLinkRow>, meta: TrackingAnalyticsPageMeta}>
     */
    public function links(array $query, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->request('listTrackingAnalyticsLinks', $query, $options);

        /** @var ApiResponse<array{data: list<TrackingAnalyticsLinkRow>, meta: TrackingAnalyticsPageMeta}> $response */
        return $response;
    }

    /**
     * @param TrackingAnalyticsListQuery $query
     * @return ApiResponse<array{data: list<TrackingAnalyticsPostRow>, meta: TrackingAnalyticsPageMeta}>
     */
    public function posts(array $query, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->request('listTrackingAnalyticsPosts', $query, $options);

        /** @var ApiResponse<array{data: list<TrackingAnalyticsPostRow>, meta: TrackingAnalyticsPageMeta}> $response */
        return $response;
    }

    /**
     * @param TrackingAnalyticsListQuery $query
     * @return ApiResponse<array{data: list<TrackingAnalyticsSourceRow>, meta: TrackingAnalyticsPageMeta}>
     */
    public function sources(array $query, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->request('listTrackingAnalyticsSources', $query, $options);

        /** @var ApiResponse<array{data: list<TrackingAnalyticsSourceRow>, meta: TrackingAnalyticsPageMeta}> $response */
        return $response;
    }

    /**
     * @param TrackingAnalyticsQuery $query
     * @return ApiResponse<array{data: TrackingAnalyticsGeo, meta: TrackingAnalyticsResourceMeta}>
     */
    public function geo(array $query, ?CallOptions $options = null): ApiResponse
    {
        $response = $this->request('getTrackingAnalyticsGeo', $query, $options);

        /** @var ApiResponse<array{data: TrackingAnalyticsGeo, meta: TrackingAnalyticsResourceMeta}> $response */
        return $response;
    }

    /**
     * @param TrackingAnalyticsListQuery $query
     * @return Generator<int, TrackingAnalyticsLinkRow, mixed, void>
     */
    public function iterateLinks(array $query, ?CallOptions $options = null): Generator
    {
        /** @var Generator<int, TrackingAnalyticsLinkRow, mixed, void> */
        return $this->iterate('listTrackingAnalyticsLinks', $query, $options);
    }

    /**
     * @param TrackingAnalyticsListQuery $query
     * @return Generator<int, TrackingAnalyticsPostRow, mixed, void>
     */
    public function iteratePosts(array $query, ?CallOptions $options = null): Generator
    {
        /** @var Generator<int, TrackingAnalyticsPostRow, mixed, void> */
        return $this->iterate('listTrackingAnalyticsPosts', $query, $options);
    }

    /**
     * @param TrackingAnalyticsListQuery $query
     * @return Generator<int, TrackingAnalyticsSourceRow, mixed, void>
     */
    public function iterateSources(array $query, ?CallOptions $options = null): Generator
    {
        /** @var Generator<int, TrackingAnalyticsSourceRow, mixed, void> */
        return $this->iterate('listTrackingAnalyticsSources', $query, $options);
    }

    /**
     * @param TrackingAnalyticsQuery|TrackingAnalyticsListQuery $query
     * @return ApiResponse<mixed>
     */
    private function request(string $operationId, array $query, ?CallOptions $options): ApiResponse
    {
        $options ??= new CallOptions();

        return $this->call($operationId, $options->with(query: $query));
    }

    /**
     * @param TrackingAnalyticsListQuery $query
     * @return Generator<int, mixed, mixed, void>
     */
    private function iterate(string $operationId, array $query, ?CallOptions $options): Generator
    {
        $initialCursor = isset($query['cursor']) ? (string) $query['cursor'] : null;
        unset($query['cursor']);

        return Paginator::iterate(
            function (?string $cursor) use ($operationId, $query, $options): ApiResponse {
                if ($cursor !== null) {
                    $query['cursor'] = $cursor;
                }

                return $this->request($operationId, $query, $options);
            },
            $initialCursor,
        );
    }
}

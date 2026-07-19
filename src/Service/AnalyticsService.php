<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class AnalyticsService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function getAnalyticsAudience(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getAnalyticsAudience', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getAnalyticsNetworks(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getAnalyticsNetworks', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getAnalyticsSummary(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getAnalyticsSummary', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getAnalyticsTimeseries(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getAnalyticsTimeseries', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listAnalyticsPosts(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listAnalyticsPosts', $options);
    }
}

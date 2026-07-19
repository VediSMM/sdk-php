<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class WebhooksService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function createWebhook(?CallOptions $options = null): ApiResponse
    {
        return $this->call('createWebhook', $options);
    }

    /** @return ApiResponse<mixed> */
    public function deleteWebhook(?CallOptions $options = null): ApiResponse
    {
        return $this->call('deleteWebhook', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getWebhook(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getWebhook', $options);
    }

    /** @return ApiResponse<mixed> */
    public function getWebhookDelivery(?CallOptions $options = null): ApiResponse
    {
        return $this->call('getWebhookDelivery', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listWebhookDeliveries(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listWebhookDeliveries', $options);
    }

    /** @return ApiResponse<mixed> */
    public function listWebhooks(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listWebhooks', $options);
    }

    /** @return ApiResponse<mixed> */
    public function retryWebhookDelivery(?CallOptions $options = null): ApiResponse
    {
        return $this->call('retryWebhookDelivery', $options);
    }

    /** @return ApiResponse<mixed> */
    public function rotateWebhookSecret(?CallOptions $options = null): ApiResponse
    {
        return $this->call('rotateWebhookSecret', $options);
    }

    /** @return ApiResponse<mixed> */
    public function testWebhook(?CallOptions $options = null): ApiResponse
    {
        return $this->call('testWebhook', $options);
    }

    /** @return ApiResponse<mixed> */
    public function updateWebhook(?CallOptions $options = null): ApiResponse
    {
        return $this->call('updateWebhook', $options);
    }
}

<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class AuditService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function listAuditEvents(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listAuditEvents', $options);
    }
}

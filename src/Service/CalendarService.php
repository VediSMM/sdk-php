<?php

declare(strict_types=1);

namespace VediSMM\Service;

use VediSMM\Value\ApiResponse;
use VediSMM\Value\CallOptions;

final class CalendarService extends AbstractService
{
    /** @return ApiResponse<mixed> */
    public function listCalendarEvents(?CallOptions $options = null): ApiResponse
    {
        return $this->call('listCalendarEvents', $options);
    }
}

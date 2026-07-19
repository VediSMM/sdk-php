<?php

declare(strict_types=1);

namespace VediSMM\Transport;

interface TransportInterface
{
    public function send(TransportRequest $request): TransportResponse;
}

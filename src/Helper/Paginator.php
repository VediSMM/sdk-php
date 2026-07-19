<?php

declare(strict_types=1);

namespace VediSMM\Helper;

use Generator;
use VediSMM\Exception\VediSMMException;
use VediSMM\Value\ApiResponse;

final class Paginator
{
    /**
     * @param callable(?string): ApiResponse<mixed> $fetchPage
     * @return Generator<int, mixed, mixed, void>
     */
    public static function iterate(callable $fetchPage, ?string $initialCursor = null): Generator
    {
        $cursor = $initialCursor;
        $seen = [];
        if ($cursor !== null) {
            $seen[$cursor] = true;
        }

        while (true) {
            $response = $fetchPage($cursor);
            $page = $response->data;
            if (!\is_array($page) || !\is_array($page['data'] ?? null) || !\is_array($page['meta'] ?? null)) {
                throw new VediSMMException('VediSMM API returned an invalid cursor page');
            }
            foreach ($page['data'] as $item) {
                yield $item;
            }
            $next = $page['meta']['next_cursor'] ?? null;
            if ($next === null) {
                return;
            }
            if (!\is_string($next) || $next === '') {
                throw new VediSMMException('VediSMM API returned an invalid next cursor');
            }
            if (isset($seen[$next])) {
                throw new VediSMMException('VediSMM API cursor loop detected');
            }
            $seen[$next] = true;
            $cursor = $next;
        }
    }
}

<?php

declare(strict_types=1);

namespace VediSMM;

final class Operation
{
    /**
     * @param list<string> $scopes
     * @param list<string> $requestContentTypes
     * @param list<string> $responseStatuses
     * @param list<string> $capabilities
     */
    public function __construct(
        public readonly string $id,
        public readonly string $method,
        public readonly string $path,
        public readonly string $tag,
        public readonly bool $authenticated,
        public readonly array $scopes,
        public readonly array $requestContentTypes,
        public readonly array $responseStatuses,
        public readonly array $capabilities,
    ) {}

    /** @param array{method: string, path: string, tag: string, authenticated: bool, scopes: list<string>, request_content_types: list<string>, response_statuses: list<string>, capabilities: list<string>} $definition */
    public static function fromArray(string $id, array $definition): self
    {
        return new self(
            $id,
            $definition['method'],
            $definition['path'],
            $definition['tag'],
            $definition['authenticated'],
            $definition['scopes'],
            $definition['request_content_types'],
            $definition['response_statuses'],
            $definition['capabilities'],
        );
    }

    public function supports(string $capability): bool
    {
        return \in_array($capability, $this->capabilities, true);
    }
}

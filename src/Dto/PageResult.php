<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Dto;

/**
 * @template T
 */
final class PageResult
{
    /**
     * @param array<int, T> $data
     */
    public function __construct(
        public readonly int $totalCount,
        public readonly bool $hasMore,
        public readonly int $limit,
        public readonly int $offset,
        public readonly array $data,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     * @return self<mixed>
     */
    public static function fromArray(array $raw): self
    {
        return new self(
            totalCount: (int) ($raw['totalCount'] ?? 0),
            hasMore: (bool) ($raw['hasMore'] ?? false),
            limit: (int) ($raw['limit'] ?? 0),
            offset: (int) ($raw['offset'] ?? 0),
            data: $raw['data'] ?? [],
        );
    }
}

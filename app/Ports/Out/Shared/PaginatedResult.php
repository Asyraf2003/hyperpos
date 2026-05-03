<?php

declare(strict_types=1);

namespace App\Ports\Out\Shared;

/**
 * @template TItem
 */
final class PaginatedResult
{
    /**
     * @param array<int, TItem> $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
        public readonly int $perPage,
        public readonly int $currentPage,
    ) {
    }
}

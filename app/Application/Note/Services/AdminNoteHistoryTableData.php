<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\AdminNoteHistoryTableReaderPort;

final class AdminNoteHistoryTableData
{
    public function __construct(
        private readonly AdminNoteHistoryTableReaderPort $reader,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *   filters: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   pagination: array<string, int>,
     *   summary: array{label: string}
     * }
     */
    public function get(array $filters): array
    {
        return $this->reader->get($filters);
    }
}

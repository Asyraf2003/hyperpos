<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\CashierNoteHistoryTableReaderPort;

final class CashierNoteHistoryTableData
{
    public function __construct(
        private readonly CashierNoteHistoryTableReaderPort $reader,
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

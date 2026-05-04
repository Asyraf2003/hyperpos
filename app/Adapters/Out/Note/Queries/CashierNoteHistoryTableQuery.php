<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use App\Ports\Out\Note\CashierNoteHistoryTableReaderPort;

final class CashierNoteHistoryTableQuery implements CashierNoteHistoryTableReaderPort
{
    public function __construct(
        private readonly CashierNoteHistoryBaseQuery $baseQuery,
        private readonly CashierNoteHistoryRowMapper $rowMapper,
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
        $criteria = CashierNoteHistoryCriteria::fromFilters($filters);
        $paginator = $this->baseQuery->paginate($criteria);
        $items = $this->rowMapper->map($paginator->items(), $criteria);

        return [
            'filters' => [
                'date' => $criteria->anchorDateText,
                'search' => $criteria->search,
                'line_status' => $criteria->lineStatus,
            ],
            'items' => $items,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'summary' => [
                'label' => sprintf(
                    'Window kasir %s dan %s.',
                    $criteria->previousDateText,
                    $criteria->anchorDateText,
                ),
            ],
        ];
    }
}

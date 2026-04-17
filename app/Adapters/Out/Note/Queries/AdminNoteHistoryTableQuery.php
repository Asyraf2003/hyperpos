<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

final class AdminNoteHistoryTableQuery
{
    public function __construct(
        private readonly AdminNoteHistoryBaseQuery $baseQuery,
        private readonly AdminNoteHistoryRowMapper $rowMapper,
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
        $criteria = AdminNoteHistoryCriteria::fromFilters($filters);
        $rows = $this->baseQuery->fetch($criteria);
        $items = $this->rowMapper->map($rows, $criteria);

        $total = count($items);
        $lastPage = max((int) ceil($total / $criteria->perPage), 1);
        $page = min($criteria->page, $lastPage);
        $offset = ($page - 1) * $criteria->perPage;
        $pagedItems = array_values(array_slice($items, $offset, $criteria->perPage));

        return [
            'filters' => [
                'date_from' => $criteria->dateFromText,
                'date_to' => $criteria->dateToText,
                'search' => $criteria->search,
                'line_status' => $criteria->lineStatus,
            ],
            'items' => $pagedItems,
            'pagination' => [
                'page' => $page,
                'per_page' => $criteria->perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'summary' => [
                'label' => sprintf(
                    'Daftar nota admin %s sampai %s.',
                    $criteria->dateFromText,
                    $criteria->dateToText,
                ),
            ],
        ];
    }
}

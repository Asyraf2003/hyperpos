<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

final class AdminNoteHistoryTableQuery
{
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
        return [
            'filters' => [
                'date_from' => $filters['date_from'] ?? date('Y-m-d'),
                'date_to' => $filters['date_to'] ?? date('Y-m-d'),
                'search' => $filters['search'] ?? '',
                'payment_status' => $filters['payment_status'] ?? '',
                'editability' => $filters['editability'] ?? '',
                'work_summary' => $filters['work_summary'] ?? '',
            ],
            'items' => [],
            'pagination' => [
                'page' => (int) ($filters['page'] ?? 1),
                'per_page' => (int) ($filters['per_page'] ?? 10),
                'total' => 0,
                'last_page' => 1,
            ],
            'summary' => [
                'label' => 'Riwayat admin placeholder belum terhubung ke query database.',
            ],
        ];
    }
}

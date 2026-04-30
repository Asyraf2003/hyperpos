<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Support\Facades\DB;

final class AdminNoteHistoryTableQuery
{
    public function __construct(
        private readonly AdminNoteHistoryProjectionFilters $filters,
        private readonly AdminNoteHistoryProjectionItemMapper $mapper,
        private readonly CashierNoteHistoryValueFormatter $formatter,
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

        $builder = DB::table('note_history_projection')
            ->whereBetween('transaction_date', [$criteria->dateFromText, $criteria->dateToText]);

        $builder = $this->filters->applySearch($builder, $criteria->search);
        $builder = $this->filters->applyLineStatusFilter($builder, $criteria->lineStatus);

        $paginator = $builder
            ->orderByDesc('transaction_date')
            ->orderByDesc('note_id')
            ->paginate($criteria->perPage, ['*'], 'page', $criteria->page);

        $items = array_map(
            fn (object $row): array => $this->mapper->map($row),
            $paginator->items(),
        );

        return [
            'filters' => [
                'date_from' => $criteria->dateFromText,
                'date_to' => $criteria->dateToText,
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
                    'Daftar nota admin %s sampai %s.',
                    $this->formatter->date($criteria->dateFromText),
                    $this->formatter->date($criteria->dateToText),
                ),
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

final class CashierNoteHistoryBaseQuery
{
    public function __construct(
        private readonly NoteHistoryRowsQuery $rowsQuery,
    ) {
    }

    /**
     * @return array<int, object>
     */
    public function fetch(CashierNoteHistoryCriteria $criteria): array
    {
        return $this->rowsQuery->fetch(
            $criteria->previousDateText,
            $criteria->anchorDateText,
            $criteria->search,
            true,
        );
    }
}

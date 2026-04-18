<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

final class AdminNoteHistoryBaseQuery
{
    public function __construct(
        private readonly NoteHistoryRowsQuery $rowsQuery,
    ) {
    }

    /**
     * @return array<int, object>
     */
    public function fetch(AdminNoteHistoryCriteria $criteria): array
    {
        return $this->rowsQuery->fetch(
            $criteria->dateFromText,
            $criteria->dateToText,
            $criteria->search,
            false,
        );
    }
}

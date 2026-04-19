<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

final class AdminNoteHistoryWorkSummaryFilter
{
    public function matches(string $filter, int $openCount, int $doneCount, int $canceledCount): bool
    {
        return match ($filter) {
            'has_open' => $openCount > 0,
            'has_done' => $doneCount > 0,
            'has_canceled' => $canceledCount > 0,
            '' => true,
            default => true,
        };
    }
}

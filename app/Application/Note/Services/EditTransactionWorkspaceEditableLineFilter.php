<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Note\WorkItem\WorkItem;

final class EditTransactionWorkspaceEditableLineFilter
{
    public function __construct(
        private readonly WorkItemOperationalStatusResolver $lineStatuses,
    ) {
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     * @param array<string, array<string, int|string>> $settlements
     * @return list<NoteRevisionLineSnapshot>
     */
    public function filter(array $lines, array $settlements): array
    {
        return array_values(array_filter(
            $lines,
            fn (NoteRevisionLineSnapshot $line): bool => $this->isEditable($line, $settlements),
        ));
    }

    /**
     * @param array<string, array<string, int|string>> $settlements
     */
    private function isEditable(NoteRevisionLineSnapshot $line, array $settlements): bool
    {
        if ($line->status() === WorkItem::STATUS_CANCELED) {
            return false;
        }

        $key = $line->workItemRootId() ?? $line->id();
        $settlement = $settlements[$key] ?? [];
        $refunded = (int) ($settlement['refunded_rupiah'] ?? 0);
        $outstanding = (int) ($settlement['outstanding_rupiah'] ?? $line->subtotalRupiah());

        return $this->lineStatuses->resolve($outstanding, $refunded) !== WorkItemOperationalStatusResolver::STATUS_REFUND;
    }
}

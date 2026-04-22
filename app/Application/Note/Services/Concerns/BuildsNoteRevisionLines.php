<?php

declare(strict_types=1);

namespace App\Application\Note\Services\Concerns;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Note\WorkItem\WorkItem;

trait BuildsNoteRevisionLines
{
    /**
     * @return array{0:list<NoteRevisionLineSnapshot>,1:int}
     */
    private function buildLinesAndGrandTotal(string $revisionId, array $workItems): array
    {
        $lines = [];
        $grandTotal = 0;

        foreach ($workItems as $item) {
            $lineRevisionId = sprintf('%s-line-%02d', trim($revisionId), $item->lineNo());
            $lines[] = $this->mapLine($lineRevisionId, trim($revisionId), $item);
            $grandTotal += $item->subtotalRupiah()->amount();
        }

        return [$lines, $grandTotal];
    }

    private function mapLine(
        string $lineRevisionId,
        string $noteRevisionId,
        WorkItem $item,
    ): NoteRevisionLineSnapshot {
        $service = $item->serviceDetail();

        return NoteRevisionLineSnapshot::create(
            $lineRevisionId,
            $noteRevisionId,
            $item->id(),
            $item->lineNo(),
            $item->transactionType(),
            $item->status(),
            $item->subtotalRupiah()->amount(),
            $service?->serviceName(),
            $service?->servicePriceRupiah()->amount(),
            $this->linePayloads->map($item),
        );
    }
}
